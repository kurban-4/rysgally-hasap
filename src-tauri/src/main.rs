#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent; // Добавляем этот импорт
use tauri::Manager;

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
.setup(|app| {
            let handle = app.handle();
            let resource_dir = handle.path().resource_dir().expect("Failed to get resource dir");
            
            // ИСПРАВЛЕНИЕ: На Mac ресурсы лежат сразу в корне resource_dir
            let project_dir = if cfg!(target_os = "macos") {
                resource_dir.join("SirinNabat")
            } else {
                resource_dir.join("resources").join("SirinNabat")
            };

            let php_ini = resource_dir.join("binaries/php.ini");
            let db_path = project_dir.join("database/database.sqlite");

            // Запускаем через встроенный сервер PHP напрямую, это надежнее чем artisan serve
            let sidecar_command = handle.shell().sidecar("php")
                .expect("Failed to create sidecar command")
                .args([
                    "-c", php_ini.to_str().unwrap(),
                    "-S", "127.0.0.1:8000", 
                    "-t", project_dir.join("public").to_str().unwrap(),
                    project_dir.join("server.php").to_str().unwrap() // Эмуляция Apache/Nginx для Laravel
                ])
                .current_dir(&project_dir)
                .env("DB_CONNECTION", "sqlite")
                .env("DB_DATABASE", db_path.to_str().unwrap())
                .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                .env("APP_ENV", "production")
                .env("APP_DEBUG", "false");

            let (mut rx, _child) = sidecar_command.spawn()
                .expect("Failed to spawn PHP sidecar");

            tauri::async_runtime::spawn(async move {
                while let Some(event) = rx.recv().await {
                    match event {
                        CommandEvent::Stdout(line) => println!("PHP: {}", String::from_utf8_lossy(&line)),
                        CommandEvent::Stderr(line) => eprintln!("PHP ERR: {}", String::from_utf8_lossy(&line)),
                        _ => (),
                    }
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}