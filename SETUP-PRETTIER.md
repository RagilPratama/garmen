# Setup Prettier Otomatis Saat Save

## Instalasi Dependencies

```bash
npm install
```

## Konfigurasi Editor

### 1. VS Code (Direkomendasikan)

1. Install extension **"Prettier - Code formatter"** (esbenp.prettier-vscode)
2. Buka Settings (`Ctrl+,` atau `Cmd+,`)
3. Cari "format on save" dan centang
4. Cari "default formatter" dan pilih "Prettier - Code formatter"

Atau buat file `.vscode/settings.json` di root project:

```json
{
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.codeActionsOnSave": {
        "source.fixAll.eslint": "explicit"
    },
    "[vue]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },
    "[javascript]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },
    "[typescript]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },
    "[json]": {
        "editor.defaultFormatter": "esbenp.prettier-vscode"
    },
    "prettier.configPath": ".prettierrc"
}
```

### 2. WebStorm/PhpStorm

1. Buka **Settings > Tools > Actions on Save**
2. Centang **"Run Prettier"**
3. Pastikan Prettier package sudah terdeteksi

### 3. Sublime Text

1. Install package **"JsPrettier"** via Package Control
2. Atur format on save di preferences

### 4. Atom

1. Install package **"prettier-atom"**
2. Aktifkan format on save di settings

## Script yang Tersedia

### Format semua file

```bash
npm run format
```

### Cek format tanpa mengubah

```bash
npm run format:check
```

### Format otomatis sebelum commit (Husky)

Prettier akan berjalan otomatis sebelum commit berkat Husky hooks.

## Konfigurasi Prettier

Konfigurasi Prettier sudah ada di `.prettierrc`:

```json
{
    "semi": true,
    "singleQuote": true,
    "tabWidth": 4,
    "printWidth": 200,
    "htmlWhitespaceSensitivity": "ignore",
    "singleAttributePerLine": false,
    "vueIndentScriptAndStyle": false
}
```

## File yang Diformat

Prettier akan memformat file dengan ekstensi:

- `.vue` (Vue components)
- `.js` (JavaScript)
- `.ts` (TypeScript)
- `.json` (JSON files)
- `.css` (CSS)
- `.scss` (SCSS)
- `.md` (Markdown)

## Troubleshooting

### Prettier tidak berjalan otomatis

1. Pastikan extension Prettier terinstall
2. Cek settings "format on save" sudah aktif
3. Restart editor

### Format tidak sesuai

1. Pastikan file `.prettierrc` ada di root project
2. Cek konfigurasi di editor tidak override `.prettierrc`

### Husky tidak berjalan

1. Jalankan `npm run prepare` untuk setup Husky
2. Pastikan folder `.husky` ada di root project

## Catatan

- Prettier akan memformat file saat disimpan (save)
- Format juga berjalan otomatis sebelum commit git
- Konfigurasi bisa disesuaikan di `.prettierrc` sesuai kebutuhan
