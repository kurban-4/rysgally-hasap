// src-tauri/src/main.rs
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::process::Command;
use std::thread;

fn main() {
    // Запускаем PHP сервер в отдельном потоке, чтобы он не вешал окно
thread::spawn(|| {
    let mut child = Command::new("php")
        .args(["artisan", "serve", "--host", "0.0.0.0", "--port", "8000"])
        .current_dir("../") // Это более правильный способ указать папку
        .spawn()
        .expect("Не удалось запустить PHP сервер");
    
    let _ = child.wait();
});

    tauri::Builder::default()
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}