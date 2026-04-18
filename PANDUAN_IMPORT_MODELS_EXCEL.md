# 📊 Panduan Import Models dari Excel

Kamu punya file `model.xlsx` dengan daftar model produk? Ikuti panduan ini untuk import ke database.

---

## 📋 Format File Excel

File `model.xlsx` harus memiliki struktur:

| Column A   | Column B                   |
| ---------- | -------------------------- |
| nama_model | keterangan                 |
| 815        | Deskripsi model 815        |
| 812 ACMR   | Deskripsi model 812 ACMR   |
| 812 ACMC/R | Deskripsi model 812 ACMC/R |
| ...        | ...                        |

**Catatan:**

- Column A: Nama model (WAJIB)
- Column B: Keterangan (OPSIONAL)
- Row 1: Header (akan di-skip)
- Baris kosong akan di-skip otomatis

---

## 🚀 Cara Import

### **Opsi 1: Via Seeder (RECOMMENDED)**

Jalankan command:

```bash
php artisan db:seed --class=ImportModelsFromExcelSeeder
```

**Keuntungan:**

- ✅ Otomatis
- ✅ Cek duplikat (tidak akan import model yang sudah ada)
- ✅ Bisa di-customize

---

### **Opsi 2: Via Tinker (Manual)**

```bash
php artisan tinker
```

Kemudian:

```php
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('model.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

foreach ($rows as $index => $row) {
    if ($index === 0) continue; // Skip header

    $namaModel = trim($row[0] ?? '');
    $keterangan = trim($row[1] ?? '');

    if (empty($namaModel)) continue;

    DB::table('master_models')->insert([
        'nama_model' => $namaModel,
        'keterangan' => $keterangan ?: null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

echo "Selesai!";
```

---

## 📝 Contoh Output

```
✓ Model '815' berhasil diimport
✓ Model '812 ACMR' berhasil diimport
✓ Model '812 ACMC/R' berhasil diimport
⊘ Model '812 ACMR' sudah ada, skip
✓ Model '812 ACMD' berhasil diimport
...

✅ Import selesai!
   Berhasil: 150
   Skipped: 5
```

---

## ⚙️ Customize Seeder

Jika ingin modify seeder, edit file:

```
database/seeders/ImportModelsFromExcelSeeder.php
```

Contoh: Tambah kolom harga_satuan dari Excel

```php
// Di dalam loop foreach:
$hargaSatuan = (float) ($row[2] ?? 0); // Column C

DB::table('master_models')->insert([
    'nama_model'   => $namaModel,
    'keterangan'   => $keterangan ?: null,
    'harga_satuan' => $hargaSatuan, // Tambah ini
    'created_at'   => now(),
    'updated_at'   => now(),
]);
```

---

## 🔍 Verifikasi Hasil

Setelah import, cek:

```bash
php artisan tinker
```

```php
// Lihat total models
DB::table('master_models')->count();

// Lihat beberapa model
DB::table('master_models')->limit(5)->get();

// Cari model tertentu
DB::table('master_models')->where('nama_model', 'like', '%815%')->get();
```

---

## ❌ Troubleshooting

### Error: "File model.xlsx tidak ditemukan"

**Solusi:** Pastikan file `model.xlsx` ada di root project (sama level dengan `artisan`)

### Error: "Class not found: PhpOffice\PhpSpreadsheet"

**Solusi:** Install package

```bash
composer require phpoffice/phpspreadsheet
```

### Model tidak terimport

**Solusi:** Cek format Excel:

- Pastikan Column A berisi nama model
- Pastikan tidak ada baris kosong di tengah data
- Pastikan tidak ada spasi berlebih di awal/akhir

---

## 📌 Tips

1. **Backup dulu** sebelum import besar-besaran
2. **Test dengan data kecil** dulu sebelum import semua
3. **Cek duplikat** sebelum import (seeder akan skip otomatis)
4. **Gunakan nama model yang konsisten** (jangan ada typo)

---

## 🎯 Next Steps

Setelah import models, kamu bisa:

1. ✅ Tambah Proses Finishing (dengan harga satuan per model)
2. ✅ Tambah Barang Masuk Kantor (model akan auto-populate)
3. ✅ Jual Gudang (model akan auto-populate)

---

**Butuh bantuan? Cek file seeder di `database/seeders/ImportModelsFromExcelSeeder.php`**
