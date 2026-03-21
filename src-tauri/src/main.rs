#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent;
use tauri::Manager;
use std::path::PathBuf;

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
        .plugin(
            tauri_plugin_updater::Builder::new()
            .pubkey("RWQGoyosV2pIpN1nsc7pzzMMJLEj5gqkZF5yNBlktv7wfduk6yWX/J/o")
            .build()
        )
        .setup(|app| {
            let handle = app.handle().clone();
            let curr_dir = std::env::current_dir().unwrap_or_else(|_| PathBuf::from("."));

            let base_path = if cfg!(debug_assertions) {
                if curr_dir.ends_with("src-tauri") { curr_dir.clone() } else { curr_dir.join("src-tauri") }
            } else {
                match handle.path().resource_dir() {
                    Ok(p) => p,
                    Err(e) => { eprintln!("Resource dir error: {}", e); return Ok(()); }
                }
            };

            let project_dir = base_path.join("resources").join("SirinNabat");
            let php_ini = base_path.join("binaries").join("php.ini");
            let db_path = project_dir.join("database").join("database.sqlite");

            println!("Project folder: {:?}", project_dir);
            println!("Project exists: {}", project_dir.exists());

            // 1. MIGRATE
            if let Ok(migrate) = handle.shell().sidecar("php") {
                let migrate = migrate
                    .args(["-c", php_ini.to_str().unwrap_or_default(), "artisan", "migrate", "--force"])
                    .current_dir(&project_dir)
                    .env("DB_CONNECTION", "sqlite")
                    .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                    .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                    .env("APP_ENV", "production")
                    .env("APP_DEBUG", "false");

                if let Ok((mut rx, _)) = migrate.spawn() {
                    tauri::async_runtime::block_on(async {
                        while let Some(event) = rx.recv().await {
                            match event {
                                CommandEvent::Stdout(line) => println!("MIGRATE: {}", String::from_utf8_lossy(&line)),
                                CommandEvent::Stderr(line) => eprintln!("MIGRATE ERR: {}", String::from_utf8_lossy(&line)),
                                CommandEvent::Terminated(_) => break,
                                _ => (),
                            }
                        }
                    });
                }
            }

            // 2. PHP SERVER
            if let Ok(sidecar) = handle.shell().sidecar("php") {
                let sidecar = sidecar
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

                if let Ok((mut rx, _)) = sidecar.spawn() {
                    tauri::async_runtime::spawn(async move {
                        while let Some(event) = rx.recv().await {
                            match event {
                                CommandEvent::Stdout(line) => println!("PHP: {}", String::from_utf8_lossy(&line)),
                                CommandEvent::Stderr(line) => eprintln!("PHP ERR: {}", String::from_utf8_lossy(&line)),
                                _ => (),
                            }
                        }
                    });
                }
            }

            // 3. UPDATER
            let handle_upd = handle.clone();
            tauri::async_runtime::spawn(async move {
                use tauri_plugin_updater::UpdaterExt;
                std::thread::sleep(std::time::Duration::from_secs(10));
                if let Ok(updater) = handle_upd.updater() {
                    if let Ok(Some(update)) = updater.check().await {
                        println!("Обновление: {}", update.version);
                        let _ = update.download_and_install(|_, _| {}, || {}).await;
                    }
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}