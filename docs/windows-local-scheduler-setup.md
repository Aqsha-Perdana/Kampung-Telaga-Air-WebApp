# Windows Local Scheduler Setup

Gunakan setup ini agar Laravel scheduler tetap berjalan di lokal Windows tanpa perlu membiarkan terminal `php artisan schedule:work` hidup terus.

## File helper

Script yang disediakan:

- `scripts/windows/run-laravel-scheduler.ps1`

Script ini akan:

- menjalankan `php artisan schedule:run --no-interaction`
- menulis output ke `storage/logs/scheduler-run.log`
- cocok dipanggil oleh Windows Task Scheduler setiap menit

## Setup di Windows Task Scheduler

1. Buka **Task Scheduler**
2. Pilih **Create Task**
3. Isi:
   - **Name**: `Telaga Air Laravel Scheduler`
   - centang **Run whether user is logged on or not** jika perlu
4. Tab **Triggers**
   - klik **New**
   - pilih **Daily**
   - centang **Repeat task every**: `1 minute`
   - **for a duration of**: `Indefinitely`
5. Tab **Actions**
   - klik **New**
   - **Program/script**:
     - `C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe`
   - **Add arguments**:
     - `-ExecutionPolicy Bypass -File "C:\xampp\htdocs\Telaga-Air\scripts\windows\run-laravel-scheduler.ps1"`
6. Tab **Start in**:
   - `C:\xampp\htdocs\Telaga-Air`
7. Simpan task

## Cara kerja setelah aktif

Laravel scheduler akan menjalankan job yang sudah terdaftar di `app/Console/Kernel.php`, termasuk:

- `payments:sync-gateway-fees --days=14 --limit=150`

Job itu dijadwalkan setiap 30 menit. Karena Task Scheduler memanggil `schedule:run` setiap 1 menit, job fee gateway akan tetap dieksekusi otomatis sesuai interval Laravel.

## Manual check

Kalau ingin cek manual:

```powershell
php artisan schedule:list
php artisan payments:sync-gateway-fees --dry-run --days=30 --limit=20
php artisan payments:sync-gateway-fees --provider=stripe --order=ORD-20260409-00001
```

## Dampak operasional

Keuntungan:

- fee gateway aktual bisa masuk ke database tanpa harus membuka menu `Sales Record`
- status `Estimated` bisa otomatis berubah ke `Actual`
- tetap aman karena mekanisme sync saat halaman dibuka masih menjadi fallback

Batasan:

- laptop/PC lokal harus menyala agar Task Scheduler berjalan
- kalau internet ke Stripe/Xendit sedang gagal, order akan tetap `Estimated` sampai run berikutnya
- ada jeda sesuai jadwal sync, jadi perubahan tidak selalu real-time detik itu juga

## Catatan

Untuk development lokal, setup ini lebih stabil daripada mengandalkan browser/admin membuka halaman tertentu. Untuk staging/production, idealnya scheduler dijalankan oleh cron atau process manager di server.
