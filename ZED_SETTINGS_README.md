# 🎨 Zed Editor Settings

Settings Zed Editor yang sudah disesuaikan dari Kiro IDE.

## ✅ Settings Sudah Applied

Settings telah otomatis di-apply ke:

```
~/Library/Application Support/Zed/settings.json
```

## 🔄 Cara Re-apply Settings (jika perlu)

### Otomatis (Recommended):

```bash
cd /Users/ragilpratama/Desktop/Learn/garmen
./apply-zed-settings.sh
```

### Manual:

1. Buka Zed Editor
2. Tekan `Cmd + ,` untuk buka Settings
3. Klik `Open Settings JSON` atau edit langsung
4. Copy isi dari `zed-settings.json` ke settings Zed
5. Save dan restart Zed

## 📋 Settings Overview

### Font Settings:

- **Font Family**: Fira Code
- **Font Size**: 13px
- **Ligatures**: ✅ Enabled
- **Terminal Font**: Fira Code 13px

### Editor Settings:

- **Tab Size**: 4 spaces
- **Hard Tabs**: Disabled (pakai spaces)
- **Line Length**: 120 characters
- **Format on Save**: ✅ Enabled
- **Auto Save**: On focus change

### Theme:

- **Mode**: Dark
- **Dark Theme**: One Dark
- **Light Theme**: One Light

### Git:

- **Git Gutter**: Enabled
- **Inline Blame**: ✅ Enabled

### Language-Specific:

#### Vue:

- Tab size: 4
- Format on save: ON
- Prettier settings:
    - Print width: 120
    - Single attribute per line: false
    - Bracket same line: true
    - HTML whitespace sensitivity: ignore
    - Vue indent script/style: false

#### JavaScript/TypeScript:

- Tab size: 4
- Format on save: ON
- Print width: 120

#### PHP:

- Tab size: 4
- Format on save: ON

#### JSON:

- Tab size: 4
- Format on save: ON

### UI Settings:

- **Scrollbar**: Always visible
- **Relative Line Numbers**: Disabled
- **Vim Mode**: Disabled
- **Breadcrumbs**: ✅ Enabled
- **Quick Actions**: ✅ Enabled
- **File Tree**: Left dock, 280px width

### Features:

- **Inline Completions**: ✅ Enabled (Supermaven)
- **Auto Close Brackets**: ✅ Enabled

## 🔧 Install Fira Code Font (jika belum)

### macOS:

```bash
brew tap homebrew/cask-fonts
brew install --cask font-fira-code
```

### Nerd Font (dengan icon support):

```bash
brew install --cask font-fira-code-nerd-font
```

Setelah install, restart Zed.

## 📝 Files

- `zed-settings.json` - Template settings Zed
- `apply-zed-settings.sh` - Script auto-apply settings
- `ZED_SETTINGS_README.md` - Dokumentasi ini

## 🎯 Mapping dari Kiro IDE

| Kiro IDE                         | Zed Editor                               | Status |
| -------------------------------- | ---------------------------------------- | ------ |
| `editor.fontFamily: 'Fira Code'` | `buffer_font_family: "Fira Code"`        | ✅     |
| `editor.fontSize: 13`            | `buffer_font_size: 13`                   | ✅     |
| `editor.fontLigatures: true`     | `buffer_font_features: { "calt": true }` | ✅     |
| `editor.tabSize: 4`              | `tab_size: 4`                            | ✅     |
| `editor.formatOnSave: true`      | `format_on_save: "on"`                   | ✅     |
| `prettier.printWidth: 120`       | `preferred_line_length: 120`             | ✅     |
| `editor.bracketPairColorization` | Built-in                                 | ✅     |
| `git.autofetch: true`            | Built-in                                 | ✅     |

## ⚠️ Perbedaan dengan VSCode/Kiro

1. **Extensions**: Zed minimal extensions, most features built-in
2. **Prettier**: Via CLI atau built-in formatter (bukan extension)
3. **ESLint**: Via LSP (limited vs VSCode)
4. **Themes**: Built-in themes only
5. **Performance**: ⚡ Much faster (Rust-based)

## 🚀 Next Steps

1. ✅ Settings sudah applied
2. ⬜ Restart Zed Editor (`Cmd + Q` lalu buka lagi)
3. ⬜ Test font ligatures (ketik `=>`, `!=`, `===`)
4. ⬜ Test format on save (edit file Vue/JS lalu save)
5. ⬜ Verify tab size (press Tab harus 4 spaces)

## 📞 Troubleshooting

### Font tidak muncul?

```bash
# Verify Fira Code installed
fc-list | grep -i fira
```

### Settings tidak apply?

```bash
# Re-run script
./apply-zed-settings.sh

# Check settings location
open ~/Library/Application\ Support/Zed/
```

### Format on save tidak jalan?

- Install Prettier globally: `npm install -g prettier`
- Atau via project: `npm install -D prettier`

---

**Settings ready! Restart Zed untuk apply changes.** 🎉
