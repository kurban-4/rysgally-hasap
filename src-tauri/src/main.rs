#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent;
use tauri::Manager;
use std::path::PathBuf;

fn main() {
    tauri::Builder::default()
        .plugin(
    tauri_plugin_updater::Builder::new()
    .pubkey("dW50cnVzdGVkIGNvbW1lbnQ6IG1pbmlzaWduIHB1YmxpYyBrZXk2QTU3MkMyQUFCMDYKdRR3F5b3NWMnBJcE4xbnNjN3B6ek1NSkxFajVncWtaRjV5TkJsa3R2N3dmZHVrNnlXWC9KL28K")
    .build()
)
        .setup(|app| {
            let handle = app.handle().clone();

            let curr_dir = std::env::current_dir().unwrap_or_else(|_| PathBuf::from("."));

            let base_path = if cfg!(debug_assertions) {
                if curr_dir.ends_with("src-tauri") {
                    curr_dir.clone()
                } else {
                    curr_dir.join("src-tauri")
                }
            } else {
                handle.path().resource_dir().expect("Failed to get resource dir")
            };

            let project_dir = base_path.join("resources").join("SirinNabat");
            let php_ini = base_path.join("binaries").join("php.ini");
            let db_path = project_dir.join("database").join("database.sqlite");

            println!("--- ПРОВЕРКА ПУТЕЙ ---");
            println!("Project folder: {:?}", project_dir);
            println!("Project exists: {}", project_dir.exists());
            println!("PHP ini exists: {}", php_ini.exists());
            println!("----------------------");

            // 1. MIGRATE
            let migrate = handle.shell().sidecar("php")
                .expect("Sidecar not found")
                .args([
                    "-c", php_ini.to_str().unwrap_or_default(),
                    "artisan", "migrate", "--force"
                ])
                .current_dir(&project_dir)
                .env("DB_CONNECTION", "sqlite")
                .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                .env("APP_ENV", "production")
                .env("APP_DEBUG", "false");

            let (mut migrate_rx, _) = migrate.spawn().expect("Failed to run migrate");

            tauri::async_runtime::block_on(async {
                while let Some(event) = migrate_rx.recv().await {
                    match event {
                        CommandEvent::Stdout(line) => println!("MIGRATE: {}", String::from_utf8_lossy(&line)),
                        CommandEvent::Stderr(line) => eprintln!("MIGRATE ERR: {}", String::from_utf8_lossy(&line)),
                        CommandEvent::Terminated(_) => break,
                        _ => (),
                    }
                }
            });

            // 2. PHP SERVER
            let sidecar_command = handle.shell().sidecar("php")
                .expect("Sidecar 'php' not found")
                .args([
                    "-c", php_ini.to_str().unwrap_or_default(),
                    "-S", "127.0.0.1:8000",
                    "-t", project_dir.join("public").to_str().unwrap_or_default(),
                    project_dir.join("server.php").to_str().unwrap_or_default()
                ])
                .current_dir(&project_dir)
                .env("DB_CONNECTION", "sqlite")
                .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                .env("APP_ENV", "production")
                .env("APP_DEBUG", "false");

            let (mut rx, _child) = sidecar_command.spawn()
                .expect("Failed to spawn PHP");

            tauri::async_runtime::spawn(async move {
                while let Some(event) = rx.recv().await {
                    match event {
                        CommandEvent::Stdout(line) => println!("PHP LOG: {}", String::from_utf8_lossy(&line)),
                        CommandEvent::Stderr(line) => eprintln!("PHP ERR: {}", String::from_utf8_lossy(&line)),
                        _ => (),
                    }
                }
            });
            // 3. ПРОВЕРКА ОБНОВЛЕНИЙ В ФОНЕ
let handle_upd = handle.clone();
tauri::async_runtime::spawn(async move {
    use tauri_plugin_updater::UpdaterExt;
    std::thread::sleep(std::time::Duration::from_secs(10));
    if let Ok(updater) = handle_upd.updater() {
        if let Ok(Some(update)) = updater.check().await {
            println!("Найдено обновление: {}", update.version);
            let _ = update.download_and_install(|_, _| {}, || {}).await;
        }
    }
});
            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}