# QAD Monitoring

Aplikasi internal untuk memonitor penggunaan QAD berdasarkan data user, utilisasi, dan aktivitas session.

## Versi Singkat untuk Presentasi

> QAD Monitoring adalah dashboard internal untuk melihat siapa saja user QAD, status user, tingkat pemakaian QAD, jumlah session, detail utilisasi, dan aktivitas raw session. Saat ini fitur aktif menggunakan tiga sumber data, yaitu `usr_mstr`, `user_activity`, dan `user_logs`. Fitur application usage masih maintenance karena tabel aplikasi belum tersedia.

Kalimat intinya:

```text
USER -> ACTIVITY -> UTILIZATION -> APPLICATION -> REPORT
```

Implementasi yang sudah aktif saat ini:

```text
USER -> ACTIVITY -> UTILIZATION
```

Tahap berikutnya menunggu data application/program.

## Tujuan Aplikasi

QAD Monitoring dibuat untuk membantu administrator dan management menjawab pertanyaan berikut:

1. Siapa saja user QAD yang terdaftar?
2. Apa status setiap user, active atau inactive?
3. User mana yang benar-benar memiliki data penggunaan QAD?
4. Berapa total session dan hari aktif user?
5. User mana yang memiliki utilisasi tertinggi?
6. Bagaimana perbandingan penggunaan QAD antar user?
7. Program QAD apa yang digunakan user?
8. Bagaimana pola login dan session berdasarkan tanggal atau waktu?

## Status Fitur

| Fitur | Status | Sumber Data | Keterangan |
|---|---|---|---|
| Dashboard | Aktif | `usr_mstr`, `user_activity` | Summary user dan utilisasi |
| User List | Aktif | `usr_mstr` | Search, filter, pagination, dan link detail |
| User Detail | Aktif | `usr_mstr`, `user_activity` | Profil user dan ringkasan utilisasi |
| Utilization | Aktif | `usr_mstr`, `user_activity` | Filter tahun/user dan pagination |
| Session Analysis | Aktif | `user_logs` | Filter tanggal/user dan pagination |
| Application Usage | Maintenance | Application/program data | Menunggu tabel sumber aplikasi |
| Reports | Maintenance | Session dan application data | Diaktifkan setelah sumber data lengkap |

### Arti Maintenance

Maintenance pada aplikasi bukan berarti error. Halaman sudah disiapkan, tetapi belum menampilkan angka atau data palsu karena sumber tabelnya belum ada di database `qad`.

## Modul Aplikasi

### 1. Dashboard

URL:

```text
/
```

Dashboard adalah halaman overview untuk melihat kondisi QAD secara cepat.

Informasi yang ditampilkan:

- Total Users
- Active Users
- Total Sessions
- Linked Users
- Trend sessions per tahun
- Perbandingan active dan inactive users
- Top user berdasarkan total sessions
- Last activity
- Days active
- Average session per day
- Average hours per week

Semua angka dashboard berasal dari query database. Tidak ada dummy data.

### 2. User List

URL:

```text
/users
```

User List menggunakan `usr_mstr` sebagai sumber master user.

Informasi yang ditampilkan:

- User ID
- User Name
- Database
- Status
- Last Logon
- User Type
- Generic User
- Email

Fitur:

- Search berdasarkan user ID, nama, atau email
- Filter status
- Filter database
- Filter user type
- Pagination 20 data per halaman
- Link ke User Detail

Nama file view:

```text
resources/views/users/users.blade.php
```

### 3. User Detail

URL:

```text
/users/{userId}
```

User Detail dibuka ketika user dipilih dari User List. Halaman ini bukan menu sidebar terpisah.

Data yang digabungkan:

```text
usr_mstr.user_id
        +
user_activity.user_id
```

Informasi yang ditampilkan:

- User ID
- User Name
- Status
- Database
- Email
- Security Group
- User Type
- Generic User
- First Login
- Last Logout
- Total Sessions
- Days Active
- Average Session per Day
- Sessions at Peak
- Average Hours per Week

### 4. Utilization

URL:

```text
/utilization
```

Utilization digunakan untuk membandingkan tingkat pemakaian QAD antar user.

Metrik:

- Total Sessions
- Days Active
- Average Session per Day
- Average Hours per Week

Fitur:

- Filter tahun
- Filter user
- Sorting berdasarkan total sessions
- Pagination 20 data per halaman
- Link ke User Detail

### 5. Session Analysis

URL:

```text
/session-analysis
```

Status saat ini: **Aktif**.

Fitur yang direncanakan:

- Activity by date
- Activity by time
- User activity
- Session detail
- Filter tanggal
- Filter user
- Filter duration
- Pagination data raw session

Fitur ini memakai tabel `user_logs` yang berisi 59.070 record raw session.

Filter yang tersedia:

- Tanggal mulai
- Tanggal akhir
- User ID atau nama user

Data ditampilkan dengan pagination agar puluhan ribu record tidak dimuat sekaligus.

Secara default, Session Analysis hanya membaca satu hari terakhir berdasarkan tanggal aktivitas terbaru yang tersedia di `user_logs`. Histori lama tidak ikut memenuhi halaman dan chart secara default, sehingga traffic query tetap ringan. Periode dapat diperlebar melalui filter tanggal.

### 6. Application Usage

URL:

```text
/application-usage
```

Status saat ini: **Maintenance**.

Fitur yang direncanakan:

- Total applications
- Most used application
- Total application users
- Top used applications
- Search program
- Filter tahun
- Sorting
- Pagination
- Relasi program dengan user

Fitur belum diaktifkan karena tabel application/program belum tersedia di database saat ini.

### 7. Reports

URL:

```text
/reports
```

Status saat ini: **Maintenance**.

Rencana report:

- User Report
- Utilization Report
- Session Report
- Application Report
- Filter tahun
- Filter tanggal
- Filter user
- Filter database
- Export Excel jika diperlukan

Reports menunggu sumber data session dan application tersedia.

## Sumber Data

### `usr_mstr`

`usr_mstr` adalah master data user QAD. Fungsinya sebagai sumber informasi identitas dan status user.

Kolom penting:

- `tahun`
- `user_id`
- `user_name`
- `database_name`
- `active`
- `country_code`
- `last_logon_date`
- `security_groups`
- `email_address`
- `user_type`
- `generic_user`

### `user_activity`

`user_activity` adalah data agregasi aktivitas dan utilisasi user.

Kolom penting:

- `tahun`
- `user_id`
- `first_login_date`
- `last_logout_date`
- `total_sessions`
- `days_active`
- `avg_sess_per_day`
- `sess_at_peak`
- `avg_hrs_per_wk`

### `user_logs`

`user_logs` adalah sumber raw session yang sudah tersedia dan digunakan oleh Session Analysis.

Kolom yang diharapkan:

- `tahun`
- `in_day`
- `in_date`
- `time_in`
- `out_day`
- `out_date`
- `time_out`
- `user_id`
- `sessions`
- `over_value`
- `duration`
- `lg_file_name`
- `database_name`
- `sid`

Status: sudah tersedia pada database aktif saat ini.

### Application / Program Data

Data aplikasi akan digunakan untuk mengetahui program QAD yang dipakai user.

Data yang diharapkan:

- Date
- Tahun date
- Program
- User ID
- Nama
- Kode program
- Identif program
- Used program
- Jumlah pengguna

Status: belum tersedia sebagai tabel pada database aktif saat ini.

## Cara Relasi Data

Relasi yang digunakan adalah logical relationship berdasarkan `user_id`, bukan foreign key formal.

```text
usr_mstr.user_id
       |
       +---- user_activity.user_id
       |
    +---- user_logs.user_id
       |
       +---- application.user_id
```

Relasi hanya digunakan jika nilai `user_id` benar-benar cocok.

## Perbedaan `usr_mstr` dan `user_activity`

### `usr_mstr`

Berisi seluruh user QAD yang terdaftar di master.

User bisa saja ada di master tetapi belum pernah memiliki data utilisasi.

### `user_activity`

Hanya berisi user yang memiliki catatan aktivitas/utilisasi.

Karena itu jumlah user kedua tabel tidak harus sama.

Contoh kondisi yang benar:

```text
Total user master       : 81 user unik
User dengan utilization : 72 user unik
User tanpa utilization  : 9 user
```

### Status User Terbaru

Karena `usr_mstr` dapat memiliki beberapa record historis untuk user yang sama, aplikasi memakai record dengan `id` terbaru per `user_id` untuk menentukan status terkini.

Aturan ini digunakan oleh:

- Dashboard
- User List
- User Detail

Dengan begitu status active/inactive konsisten di seluruh halaman.

## Alur User

```text
1. Administrator membuka Dashboard
2. Melihat summary kondisi QAD
3. Membuka User List
4. Mencari atau memfilter user
5. Memilih User ID
6. Melihat User Detail
7. Melihat total session dan utilisasi
8. Membuka Utilization untuk membandingkan user
9. Session Analysis sudah bisa digunakan; Application Usage menunggu sumber data aplikasi
10. Reports menjadi output akhir monitoring
```

## Struktur Folder Utama

```text
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── MonitoringController.php
│   └── UserController.php
├── Models/
│   ├── Usrqad.php
│   └── UserDetail.php
└── Providers/
    └── AppServiceProvider.php

resources/
├── css/
│   └── app.css
├── js/
│   ├── app.js
│   ├── dashboard.js
│   └── sidebar.js
└── views/
    ├── layouts/
    │   └── app.blade.php
    ├── dashboard/
    │   └── index.blade.php
    ├── users/
    │   ├── users.blade.php
    │   └── show.blade.php
    ├── monitoring/
    │   ├── utilization.blade.php
    │   └── unavailable.blade.php
    └── components/
        ├── layout/
        │   └── sidebar.blade.php
        └── ui/
            └── status-pill.blade.php

routes/
└── web.php
```

## Tech Stack

- Laravel 8
- PHP 8.1 compatible
- MySQL
- Blade Template
- Bootstrap 5
- Bootstrap Icons
- Chart.js
- Laravel Mix
- Custom CSS
- Vanilla JavaScript

Tidak menggunakan React atau Vue.

## Menjalankan Aplikasi

### 1. Pastikan database aktif

Pastikan MySQL Laragon berjalan dan database `qad` tersedia.

### 2. Konfigurasi `.env`

Contoh konfigurasi:

```env
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qad
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Install dependency PHP

```powershell
composer install
```

### 4. Install dependency frontend

```powershell
npm install
```

Jika PowerShell memblokir `npm.ps1` di Windows, gunakan:

```powershell
npm.cmd install
```

### 5. Build asset

```powershell
npm.cmd run development
```

### 6. Bersihkan cache Laravel jika diperlukan

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 7. Jalankan aplikasi

```powershell
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

## Prinsip Teknis

- Tidak menggunakan dummy data.
- Statistik berasal dari database.
- Query besar menggunakan agregasi SQL.
- Tabel memakai pagination.
- `user_id` digunakan sebagai relasi logical.
- Data asli tidak diubah atau dihapus oleh UI.
- Halaman maintenance tidak menampilkan data palsu.
- Blade digunakan untuk presentasi, bukan tempat query database.
- JavaScript dipisahkan dari Blade.
- CSS dipisahkan dari markup.
- Component reusable digunakan untuk elemen UI berulang.

## Catatan untuk Senior

Poin yang bisa disampaikan saat demo:

1. Dashboard dan Utilization sudah mengambil data real dari MySQL.
2. User List menggunakan master `usr_mstr` dan memiliki search, filter, serta pagination.
3. User Detail menggabungkan master user dengan agregasi `user_activity` berdasarkan `user_id`.
4. Status user memakai record master terbaru agar konsisten.
5. User di master tidak selalu memiliki data utilization, sehingga jumlah kedua tabel memang dapat berbeda.
6. Session Analysis sudah memakai `user_logs`; Application Usage masih menunggu tabel program.
7. Aplikasi sengaja tidak membuat dummy data agar hasil monitoring tetap valid.
8. Struktur view dipisah berdasarkan domain bisnis, bukan berdasarkan nama tabel database.
9. Frontend memakai Blade, Bootstrap, Chart.js, Vanilla JavaScript, dan Custom CSS.
10. Aplikasi siap dikembangkan ketika tabel raw session dan application sudah tersedia.
