# Guidelines

# 1) Charter & Sasaran

* **Nama Proyek:** Jurnal Harian
* **Tujuan:** Membantu pengguna merencanakan hari, mengeksekusi fokus, melacak waktu, dan melakukan refleksi harian dengan cepat, ringan, dan privat.
* **Pilar Produk:** kecepatan input, keyboard-first, minim gangguan, data aman, mudah diekspor.


# 2) Lingkup Fitur (MVP)

1. **Daily Planner**

   * CRUD tugas untuk tanggal tertentu, backlog global, drag tugas ke hari.
   * Field: title, description, priority, status, planned_date, due_date, estimate_minutes, tags, project.
2. **Time Blocking**

   * Grid 05:00–24:00, buat blok 15-menit, drag-resize, validasi tabrakan.
3. **Pomodoro/Focus Session**

   * Preset 25/5, custom, notifikasi browser, auto-log hasil ke tugas.
4. **Habit Tracker**

   * CRUD kebiasaan, checklist harian, streak sederhana.
5. **Jurnal & Refleksi**

   * Catatan harian, prompt refleksi, energi, mood, daily_score.
6. **Time Tracking**

   * Start/stop timer per tugas, rekap durasi harian.
7. **Pencarian & Filter**

   * Filter by tag, project, prioritas, status, tanggal; full-text jurnal.
8. **Tema & Preferensi**

   * Dark/light via tokens Tailwind 4, pengaturan user (durasi pomodoro, tampilan default).

# 3) Kandidat Fitur Lapis 2 (pasca-MVP)

* Recurring tasks (RRULE-lite), Weekly Review, Templates hari, Export CSV/JSON, Import iCal (read-only), PWA offline, Interruption Log, Goals/Projects lanjutan.

# 4) Arsitektur & Teknologi

* **Backend:** Laravel 12 (PHP 8.2+), Pest untuk testing, Larastan level max, PHP-CS-Fixer.
* **State UI:** Livewire 4 (boleh Volt untuk komponen view-first), Alpine opsional kecil.
* **Frontend:** TailwindCSS 4 (design tokens), tanpa UI-kit berat.
* **DB:** MySQL 8/ MariaDB 10.6+, InnoDB, timezone UTC.
* **Packages:**

  * spatie/laravel-settings (preferensi user)
  * spatie/laravel-permission opsional (kalau nanti multi-role)
  * laravel/sanctum (kalau butuh API lokal).
* **Keamanan:** CSRF, rate limiting pada timer, model policies, backup otomatis.
* **Aksesibilitas:** fokus ring, tab order wajar, kontras WCAG AA.

# 5) Model Data (ringkas)

* `users`
* `projects(id,user_id,name,color)`
* `tags(id,user_id,name,color)`
* `tasks(id,user_id,project_id?,title,description?,priority[p1..p4],status[planned|in_progress|done|canceled],planned_date?,due_date?,estimate_minutes,actual_minutes,context?,is_recurring,recurrence_rule?,order,int)`
* `task_tag(task_id,tag_id)`
* `time_blocks(id,user_id,task_id?,start_at,end_at,note?)`
* `time_logs(id,user_id,task_id,started_at,ended_at?,source[manual|timer|pomodoro])`
* `habits(id,user_id,name,target_per_week,start_date,end_date?)`
* `habit_checks(id,habit_id,date,value)`
* `journal_entries(id,user_id,date,content,energy[m/l/h],mood[bad|ok|good],daily_score?)`
* `attachments(id,user_id,task_id?,path,mime,size)`

# 6) Struktur Folder

```
app/
  Http/Livewire/
    Planner/
      DailyPlannerPage.php
      TaskList.php
      TaskItem.php
    TimeBlock/TimeBlockGrid.php
    Focus/PomodoroTimer.php
    Habit/HabitWidget.php
    Journal/JournalEditor.php
    Review/WeeklyReview.php
  Models/ (Task, TimeBlock, TimeLog, Habit, HabitCheck, JournalEntry, Tag, Project)
  Policies/
  Services/
    RecurrenceService.php
    TimeLogService.php
database/
  migrations/
  seeders/
resources/
  views/livewire/...
  css/app.css (Tailwind 4)
routes/
  web.php
tests/
  Feature/
  Unit/
```

# 7) Komponen & Acceptances (MVP)

## 7.1 Daily Planner

* Menampilkan mini-calendar + backlog + daftar tugas harian.
* **DoD:**

  * Tambah/ubah/hapus tugas tanpa refresh (Livewire).
  * Drag tugas dari backlog ke tanggal.
  * Shortcut: `n` tambah, `space` toggle done, `e` edit inline.

## 7.2 Time Block Grid

* Grid 15-menit, drag-create, resize, deteksi overlap.
* **DoD:**

  * Create/update/delete blok.
  * Hubungkan blok dengan tugas (opsional).
  * Validasi tidak boleh overlap dalam kolom user.

## 7.3 Pomodoro/Focus

* 25/5, custom, notifikasi, suara pendek.
* **DoD:**

  * Start/stop, auto tulis `time_logs`.
  * “Focus mode” menyembunyikan panel non-esensial.

## 7.4 Habit

* CRUD kebiasaan, checklist harian, tampil streak.
* **DoD:**

  * Centang hari ini menambah `habit_checks`.
  * Streak bertambah bila hari berturut-turut aktif.

## 7.5 Jurnal

* Editor markdown ringan, autosave, prompt refleksi.
* **DoD:**

  * Simpan `content`, `energy`, `mood`, `daily_score`.
  * Full-text search jurnal bekerja.

## 7.6 Pencarian/Filter

* Query bar dengan operator sederhana: `tag:`, `project:`, `status:`, `date:YYYY-MM-DD`.
* **DoD:**

  * Filter kombinasi berfungsi.
  * Hasil muncul < 200 ms pada dataset 1k tugas lokal.

# 8) UX & Desain (Tailwind 4)

* **Tokens:** definisikan CSS variables untuk `--bg, --fg, --muted, --primary, --card, --ring`.
* **Dark mode:** `data-theme="dark"`.
* **Komponen:** card rounded-2xl, shadow-md, ring-1 on hover, transition-150ms.
* **Prioritas badge:**
  * Urgent : bg-red-100 ring-red-300
  * Important: bg-blue-100 ring-blue-300
  * Normal: bg-amber-100 ring-amber-300

* **Keyboard-first:** `n`, `e`, `space`, `t` (timer), `/` (search), `Shift+C` quick capture modal.

# 9) Standar Kode & Kualitas

* **Coding style:** PSR-12 + PHP-CS-Fixer.
* **Static analysis:** Larastan level max, 0 error wajib.
* **Testing:** Pest, coverage fungsional minimum untuk Planner, TimeBlock, Timer, Habit, Journal.
* **Performance:** N+1 dilarang, gunakan `->with()` dan indeks DB.
* **A11y:** Navigasi keyboard, aria-live untuk timer, kontras AA.
* **I18n:** string UI melalui lang files.

# 10) Keamanan & Privasi

* Autentikasi Laravel standar, session secure, cookie httpOnly.
* CSRF aktif di semua form Livewire.
* Backup harian, export-impor manual tersedia.
* Data milik user; hapus akun menghapus seluruh data.

# 11) Proses Kerja & Milestone

* **Sprint 0 (setup 0.5 hari):** repo, CI (PHPUnit/Pest), Tailwind 4, Larastan, CS-Fixer, auth.
* **Sprint 1 (hari 1–5):** Tasks + Backlog + Planner + TimeBlock basic + Pomodoro basic.
* **Sprint 2 (hari 6–10):** Habit + Journal + Search/Filter + Export CSV + Review mingguan sederhana.
* **Stabilisasi (hari 11–12):** Hardening, test, dokumentasi pengguna.

# 12) Logging & Telemetri Lokal

* Log event penting: create/update/delete task, start/stop timer, create block, habit check.
* Jangan kirim data keluar. Console log diminimalkan di produksi.

---

## DO

1. **Jalankan MVP persis sesuai lingkup** sebelum menambah ide baru.
2. **Prioritaskan kecepatan input**: semua aksi penting harus 1–2 klik atau 1 shortcut.
3. **Tulis test untuk alur kritikal**: CRUD task, drag ke hari, create time block, start/stop timer, habit check, simpan jurnal.
4. **Optimalkan query**: gunakan index pada `planned_date`, `status`, `user_id`; eager load relasi.
5. **Gunakan Tailwind tokens** agar dark/light konsisten.
6. **Pastikan aksesibilitas dasar**: fokus jelas, shortcut terdokumentasi, aria-live untuk timer.
7. **Sediakan ekspor data** dari awal (CSV minimal).
8. **Pisahkan concerns**: service untuk recurrence, policy untuk akses, Livewire untuk interaksi.
9. **Dokumentasikan shortcut & alur harian** di halaman Bantuan.
10. **Sediakan seeders** contoh data untuk demo dan pengujian.

## DON’T

1. **Jangan** menambah integrasi eksternal sebelum MVP stabil.
2. **Jangan** memaksa UI berat, animasi berlebihan, atau dependensi komponen UI besar.
3. **Jangan** biarkan N+1 atau query tanpa indeks di halaman planner.
4. **Jangan** menyimpan timer di frontend saja; semua sesi harus tercatat di `time_logs`.
5. **Jangan** menunda pengamanan dasar (CSRF, policy, rate limit).
6. **Jangan** menyimpan data sensitif ke localStorage tanpa enkripsi bila tidak perlu.
7. **Jangan** mengabaikan keyboard navigation. Mouse-only itu menyebalkan untuk power user.
8. **Jangan** mem-blend backlog dan planned tasks tanpa penanda jelas.
9. **Jangan** mengubah skema data tanpa migrasi terukur.
10. **Jangan** menunda testing sampai akhir sprint.

---

# 13) Kriteria Sukses (Go/No-Go)

* Menyusun rencana harian, memblok waktu, menjalankan 1 sesi fokus, mencatat jurnal, menandai habit, dan melihat rekap harian dalam ≤ 10 menit dari akun baru.
* Semua interaksi inti tanpa reload, < 200 ms perceived latency di data kecil.
* Tidak ada error Larastan, tidak ada pelanggaran CS-Fixer, test inti lulus.
* Ekspor CSV berfungsi untuk tasks dan time_logs.
* Dark/light mode konsisten.

# 14) Catatan Implementasi Kritis

* **Drag & reorder** gunakan Livewire event + penyimpanan `order` per hari.
* **Overlap time block**: validasi di server; tolak jika `exists block where start < new_end AND end > new_start`.
* **Pomodoro**: jalankan via JS timer, sinkronkan state ke Livewire tiap tick minimal, commit hasil saat selesai atau stop.
* **Search**: builder dengan scope per field; jurnal gunakan FULLTEXT MATCH AGAINST jika tersedia.
* **Recurring (fase 2)**: setiap malam jalankan job generate untuk esok hari.

# 15) Dokumentasi Pengguna (minimum)

* Halaman “Cara Pakai Cepat” berisi: 3 langkah harian, daftar shortcut, cara ekspor data, dan cara ubah tema.

Selesai. Serahkan ini ke agent, kemudian mulai Sprint 0. Jangan nyasar bikin dashboard analitik canggih dulu. Rencana, eksekusi, refleksi. Repetisi yang benar itu yang bikin hari-harimu tidak kabur begitu saja.
