#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent;
use tauri::Manager;
use std::path::PathBuf;

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
        .setup(|app| {
            let handle = app.handle();
            
            // 1. ОПРЕДЕЛЕНИЕ ПУТЕЙ
            // В режиме dev текущая папка обычно и есть 'src-tauri'
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

            // Точные пути к твоему проекту и конфигу PHP
            let project_dir = base_path.join("resources").join("SirinNabat");
            let php_ini = base_path.join("binaries").join("php.ini");
            let db_path = project_dir.join("database").join("database.sqlite");

            // Вывод в терминал для проверки (смотри его при запуске!)
            println!("--- ПРОВЕРКА ПУТЕЙ ---");
            println!("Project folder: {:?}", project_dir);
            println!("Project exists: {}", project_dir.exists());
            println!("PHP ini exists: {}", php_ini.exists());
            println!("----------------------");

            // 2. ЗАПУСК SIDECAR
            let sidecar_command = handle.shell().sidecar("php")
                .expect("Sidecar 'php' не найден в tauri.conf.json")
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
                .env("APP_ENV", "local")
                .env("APP_DEBUG", "true");

            let (mut rx, _child) = sidecar_command.spawn()
                .expect("Не удалось запустить процесс PHP");

            tauri::async_runtime::spawn(async move {
                while let Some(event) = rx.recv().await {
                    match event {
                        CommandEvent::Stdout(line) => println!("PHP LOG: {}", String::from_utf8_lossy(&line)),
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