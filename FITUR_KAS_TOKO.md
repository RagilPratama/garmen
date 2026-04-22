# Fitur Kas Toko

## Deskripsi

Sistem kas toko memungkinkan setiap toko untuk memiliki uang kas sendiri dengan tracking transaksi lengkap dan informasi real-time di dashboard.

## Fitur Utama

### 1. Saldo Kas Per Toko

- Setiap toko memiliki saldo kas independen
- Saldo tersimpan di tabel `tokos` kolom `saldo_kas`
- Saldo otomatis terupdate setiap ada transaksi

### 2. Transaksi Kas

Terdapat 2 jenis transaksi:

- **Masuk**: Penjualan, setoran awal, dll
- **Keluar**: Pengeluaran operasional, dll

Setiap transaksi mencatat:

- Tanggal transaksi
- Jenis (masuk/keluar)
- Kategori (penjualan, setoran awal, pengeluaran operasional, dll)
- Jumlah
- Keterangan
- Saldo sebelum dan sesudah transaksi
- User yang melakukan transaksi

### 3. Dashboard

Dashboard menampilkan:

- **Saldo Kas Per Toko**: Card dengan saldo terkini setiap toko
- **Transaksi Terbaru**: 5 transaksi kas terakhir
- Informasi berbeda untuk admin dan user toko

### 4. Halaman Kas Toko

Fitur lengkap untuk mengelola kas:

- Filter berdasarkan toko, tanggal, dan jenis transaksi
- Tambah transaksi kas baru
- Hapus transaksi (dengan rollback saldo otomatis)
- Pagination untuk data banyak
- Validasi saldo tidak boleh negatif

### 5. Akses Kontrol

- **Admin**: Bisa melihat dan mengelola kas semua toko
- **User Toko**: Hanya bisa melihat dan mengelola kas toko mereka sendiri

## Database

### Tabel: `tokos`

Ditambahkan kolom:

- `saldo_kas` (decimal 15,2): Saldo kas terkini

### Tabel: `kas_toko`

Tabel baru untuk mencatat transaksi:

- `id`: Primary key
- `toko_id`: Foreign key ke tabel tokos
- `tanggal`: Tanggal transaksi
- `jenis`: Enum (masuk/keluar)
- `kategori`: String kategori transaksi
- `jumlah`: Decimal jumlah transaksi
- `keterangan`: Text keterangan opsional
- `referensi`: String referensi (misal: no_nota)
- `saldo_sebelum`: Decimal saldo sebelum transaksi
- `saldo_sesudah`: Decimal saldo setelah transaksi
- `user_id`: Foreign key ke tabel users
- `created_at`, `updated_at`: Timestamps

## Routes

```php
GET  /kas-toko              - Halaman utama kas toko
POST /kas-toko              - Tambah transaksi kas
DELETE /kas-toko/{kasToko}  - Hapus transaksi kas
GET  /kas-toko/laporan      - Laporan kas (untuk future development)
```

## Cara Penggunaan

### Untuk Admin:

1. Akses menu "Kas Toko" di sidebar
2. Pilih toko yang ingin dikelola
3. Tambah transaksi dengan klik tombol "Tambah Transaksi"
4. Isi form: toko, tanggal, jenis, kategori, jumlah, keterangan
5. Lihat saldo kas per toko di dashboard

### Untuk User Toko:

1. Akses menu "Kas Toko" di sidebar
2. Tambah transaksi kas toko sendiri
3. Lihat saldo kas di dashboard
4. Filter transaksi berdasarkan tanggal dan jenis

## Validasi

- Saldo kas tidak boleh negatif saat transaksi keluar
- Jumlah transaksi harus positif
- Tanggal transaksi wajib diisi
- Kategori transaksi wajib diisi

## Keamanan

- Transaksi menggunakan database transaction untuk konsistensi data
- Lock tabel saat update saldo untuk mencegah race condition
- User toko hanya bisa akses data toko mereka sendiri
- Validasi akses di controller

## Future Development

- Laporan kas bulanan/tahunan
- Export data kas ke Excel/PDF
- Integrasi otomatis dengan penjualan (auto-create transaksi kas saat penjualan lunas)
- Grafik trend kas toko
- Notifikasi saat saldo kas rendah
- Approval workflow untuk transaksi besar

## Integrasi dengan Penjualan Toko ✅

### Otomatis Tambah Kas saat Penjualan

Sistem sekarang otomatis menambahkan transaksi kas toko saat:

1. **Penjualan Baru dengan Pembayaran Cash**
    - Saat membuat penjualan baru di `ProsesJual`
    - Jika ada pembayaran awal (`bayar > 0`)
    - Dan metode pembayaran adalah `cash`
    - Maka otomatis membuat transaksi kas masuk

2. **Pembayaran Cicilan Cash**
    - Saat menambah pembayaran cicilan di `PenjualanPembayaran`
    - Untuk channel `toko`
    - Dengan metode `cash`
    - Maka otomatis membuat transaksi kas masuk

### Detail Implementasi

**File yang Dimodifikasi:**

- `app/Http/Controllers/ProsesJualController.php`
    - Method `store()` - Tambah logika kas saat penjualan baru
- `app/Http/Controllers/PenjualanPembayaranController.php`
    - Method `store()` - Tambah logika kas saat pembayaran cicilan
    - Method `destroy()` - Rollback kas saat hapus pembayaran

**Logika Transaksi:**

```php
// Saat penjualan cash
if (metode === 'cash') {
    1. Lock toko untuk update
    2. Ambil saldo sebelum
    3. Hitung saldo sesudah = saldo sebelum + jumlah bayar
    4. Buat transaksi KasToko:
       - jenis: masuk
       - kategori: Penjualan
       - referensi: no_nota
       - keterangan: Detail penjualan
    5. Update saldo_kas toko
}
```

**Keamanan:**

- Menggunakan database transaction untuk konsistensi
- Lock toko saat update untuk mencegah race condition
- Rollback otomatis jika ada error

**Catatan Penting:**

- Hanya pembayaran **CASH** yang masuk ke kas toko
- Pembayaran **TRANSFER** dan **DEBIT** tidak masuk kas (dianggap langsung ke rekening bank)
- Saat hapus pembayaran cash, saldo kas otomatis dikembalikan

### Contoh Skenario

**Skenario 1: Penjualan Lunas Cash**

```
Penjualan: Rp 1.000.000
Bayar: Rp 1.000.000 (cash)
→ Kas toko bertambah Rp 1.000.000
```

**Skenario 2: Penjualan Cicilan**

```
Penjualan: Rp 1.000.000
Bayar DP: Rp 300.000 (cash)
→ Kas toko bertambah Rp 300.000

Cicilan 1: Rp 400.000 (cash)
→ Kas toko bertambah Rp 400.000

Cicilan 2: Rp 300.000 (transfer)
→ Kas toko TIDAK bertambah (masuk rekening)
```

**Skenario 3: Hapus Pembayaran**

```
Hapus pembayaran cash Rp 300.000
→ Kas toko berkurang Rp 300.000 (rollback)
```

## Integrasi dengan Pengeluaran Toko ✅

### Otomatis Kurangi Kas saat Pengeluaran

Sistem sekarang otomatis mengurangi saldo kas toko saat ada pengeluaran:

1. **Tambah Pengeluaran Baru**
    - Saat membuat pengeluaran baru di `PengeluaranToko`
    - Sistem otomatis mengurangi saldo kas sesuai metode pembayaran
    - Membuat transaksi kas keluar otomatis

2. **Edit Pengeluaran**
    - Saat edit pengeluaran, saldo lama dikembalikan
    - Kemudian saldo baru dikurangi
    - Transaksi kas juga diupdate

3. **Hapus Pengeluaran**
    - Saat hapus pengeluaran, saldo kas dikembalikan
    - Transaksi kas terkait juga dihapus

### Detail Implementasi

**File yang Dimodifikasi:**

- `app/Http/Controllers/PengeluaranTokoController.php`
    - Method `store()` - Kurangi kas saat pengeluaran baru
    - Method `update()` - Update kas saat edit pengeluaran
    - Method `destroy()` - Kembalikan kas saat hapus pengeluaran

**Logika Transaksi:**

```php
// Saat pengeluaran
1. Lock toko untuk update
2. Ambil saldo sebelum (sesuai metode)
3. Validasi saldo mencukupi
4. Hitung saldo sesudah = saldo sebelum - jumlah pengeluaran
5. Buat transaksi KasToko:
   - jenis: keluar
   - metode_bayar: sesuai pengeluaran
   - kategori: sesuai pengeluaran
   - referensi: PENGELUARAN-{id}
6. Update saldo_kas toko
```

**Keamanan:**

- Validasi saldo mencukupi sebelum pengeluaran
- Database transaction untuk konsistensi
- Lock toko saat update
- Rollback otomatis jika error

### Contoh Skenario

**Skenario 1: Pengeluaran Cash**

```
Saldo Cash: Rp 1.000.000
Pengeluaran: Rp 200.000 (cash) - Bayar listrik
→ Saldo Cash: Rp 800.000
→ Kas toko berkurang Rp 200.000
```

**Skenario 2: Pengeluaran Transfer**

```
Saldo Transfer: Rp 500.000
Pengeluaran: Rp 100.000 (transfer) - Bayar sewa
→ Saldo Transfer: Rp 400.000
→ Kas toko berkurang Rp 100.000
```

**Skenario 3: Edit Pengeluaran**

```
Pengeluaran lama: Rp 200.000 (cash)
Edit jadi: Rp 300.000 (transfer)
→ Saldo Cash dikembalikan +Rp 200.000
→ Saldo Transfer dikurangi -Rp 300.000
```

**Skenario 4: Hapus Pengeluaran**

```
Hapus pengeluaran Rp 200.000 (cash)
→ Saldo Cash dikembalikan +Rp 200.000
→ Transaksi kas dihapus
```

### Validasi

- Saldo per metode harus mencukupi sebelum pengeluaran
- Tidak bisa pengeluaran jika saldo tidak cukup
- Error message jelas: "Saldo {metode} tidak mencukupi"

### Tracking

- Setiap pengeluaran tercatat di kas toko dengan referensi `PENGELUARAN-{id}`
- Mudah dilacak dari mana pengeluaran berasal
- Sinkronisasi otomatis antara pengeluaran dan kas
