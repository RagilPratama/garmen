// Konfigurasi untuk menjalankan Prettier otomatis saat save
// Instruksi untuk setup di berbagai editor:

// 1. VS Code:
//    - Install extension "Prettier - Code formatter" (esbenp.prettier-vscode)
//    - Buka Settings (Ctrl+, atau Cmd+,)
//    - Cari "format on save" dan centang
//    - Cari "default formatter" dan pilih "Prettier - Code formatter"
//    - Atau tambahkan di .vscode/settings.json:
/*
{
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "[vue]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },
    "[javascript]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    }
}
*/

// 2. WebStorm/PhpStorm:
//    - Buka Settings > Tools > Actions on Save
//    - Centang "Run Prettier"
//    - Atau install plugin "Prettier" dan aktifkan format on save

// 3. Sublime Text:
//    - Install package "JsPrettier"
//    - Atur format on save di preferences

// 4. Atom:
//    - Install package "prettier-atom"
//    - Aktifkan format on save di settings

// 5. Command line (manual):
//    - npm run format       # Format semua file
//    - npm run format:check # Cek format tanpa mengubah

module.exports = {
    // Konfigurasi Prettier sudah ada di .prettierrc
    // File ini hanya untuk dokumentasi setup
};
