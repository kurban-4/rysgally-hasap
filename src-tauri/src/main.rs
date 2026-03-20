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
            let project_dir = resource_dir.join("resources").join("SirinNabat");

            // ПЕЧАТАЕМ ПУТЬ В КОНСОЛЬ ДЛЯ ПРОВЕРКИ
            println!("Брат, ищу PHP здесь: {:?}", project_dir);

let php_ini = resource_dir.join("binaries/php.ini");

let sidecar_command = handle.shell().sidecar("php")
    .expect("Failed to create sidecar command")
    .args(["-c", php_ini.to_str().unwrap(), "artisan", "serve", "--port", "8000"])
    .current_dir(&project_dir)
    .env("DB_CONNECTION", "sqlite")
    .env("DB_DATABASE", project_dir.join("database/database.sqlite").to_str().unwrap())
    .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
    .env("APP_ENV", "production")
    .env("APP_DEBUG", "false");

            let (mut rx, _child) = sidecar_command.spawn()
                .expect("Failed to spawn PHP sidecar");

            // СЛУШАЕМ ОШИБКИ PHP
            tauri::async_runtime::spawn(async move {
                while let Some(event) = rx.recv().await {
                    match event {
                        CommandEvent::Stdout(line) => println!("PHP ВЫВОД: {}", String::from_utf8_lossy(&line)),
                        CommandEvent::Stderr(line) => eprintln!("PHP ОШИБКА: {}", String::from_utf8_lossy(&line)),
                        _ => (),
                    }
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}