KITA KOLABORASI NYA DISINI YA

Apa itu Git?
	Git adalah sistem pengontrol versi (Version Control System) yang mencatat setiap perubahan pada file atau baris kode dari waktu ke waktu, diciptakan oleh Linus Torvalds selaku pencipta sistem operasi Linux. Git bukanlah sebuah singkatan melainkan sengaja dipilih olehnya.

1. Konfigurasi Git
   git config --global user.name "Username Github"
   git config --global user.email "Email@Email.com"

2. Memulai Git
   git init (Menginstall Git ke dalam projek kita di folder tersembunyi)
   git add . (Menambah semua file ke dalam Git)
   git commit -m "Komentar apa yang ditambahkan/diubah"
   git branch -M main (Buat Branch default yaitu Main, bisa custom atau nama lain, Main hanya yang biasa dipakai orang-orang)
   git remote add origin https://github.com/username/nama-repo.git (Buat menghubungkan ke Github Repository yang dituju)
   git push -u origin main (Buat upload projeknya ke Repository)

   Sebelum diupload akan dimintai Username dan juga Password
   Username : Itu username dari akun Github kita
   Password : Bukan password Github melainkan Token Akses akun Github

   Cara membuat Passwordnya :
   1. Buka Settings
   2. Scroll paling bawah dan klik Developer Settings
   3. Klik Personal akses token lalu pilih Token (Klasik)
   4. Generate new token pilih Generate new token (klasik)
   5. Klik bagian Repo lalu Generate Token

   Agar tidak ditanya-tanya mulu saat upload bisa pakai ini :
   git config --global credential.helper store (Fungsinya buat mengingat Token kita)

3. Kolaborasi
   Untuk kolaborasi kita perlu mengundang rekan yang akan diajak untuk kolaborasi
   - Buka Repository Github yang akan dijadikan tempat untuk kolaborasi
   - Klik Settings lalu klik Collaborators dan masukkan Username Github atau Email rekanmu
   - Teman akan menerina Email undangan, klik Accept

4. Kolaborasi Bersama Rekan
   git clone https://github.com/username/nama-repo.git (Clone itu fungsinya buat mendownload Repository yang dituju)
   cd NamaFolder (Masuk ke Folder)
   git checkout -b NamaBranch (Bikin Branch baru)

   Rekan kita harus membuat cabangnya masing-masing termasuk kita, jangan di utama atau Main
   git checkout -b NamaBranch (Untuk membuat Branch baru dengan nama sesuai yang diinginkan di NamaBranch)
   git push -u origin NamaBranch (Untuk mengupload perubahan penambahan Branch)
   git branch (buat memastikan kita ada di branch mana)

   Mengubah nama Branch, pastikan Branch saat ini sesuai dengan yang mau diubah
   git branch -m NamaBranchBaru (Isi dengan nama branch baru)
   
   Ubah nama branch tanpa harus pindah ke branch nya
   git branch -m NamaLama NamaBaru

   Untuk pindah branch ke yang sudah ada
   git checkout NamaBranch

5. Alur Kerja Kolaborasi
   git add .
   git commit -m "Menyelesaikan fitur ..."
   git push origin NamaBranch (Nama Branch sesuai yang sedang digunakan)

6. Merge, menggabungkan ke Main
   Setelah salah satu selesai mengerjakan tugas dan ingin menggabungkannya ke branch utama (main):
   - Buka Repository yang sesuai di GitHub.
   - Klik tab Pull requests > klik tombol hijau New pull request.
   - Atur arah penggabungan : Base: main > Compare: NamaBranch.
   - Klik Create pull request, beri judul pekerjaan yang selesai, lalu klik Create pull request lagi.
   - Klik tombol hijau Merge pull request > klik Confirm merge.
   - Sekarang branch main sudah berisi kodingan terbaru.

7. Sinkronisasi
   Jika Rekan telah Merge projeknya ke Main maka orang lain wajib ikut perubahan biar ga ketinggalan versi :

   1. Pindah ke branch utama dan ambil data terbaru dari GitHub
	  git checkout main
	  git pull origin main
	
   2. Pindah kembali ke branch kerja masing-masing
	  git checkout NamaBranch (Ke masing-masing)
	
   3. Gabungkan update terbaru dari main ke dalam branch kerja
	  git merge main

8. Merge Conflict
   Saat kode tumpang tindih akan muncul seperti ini saat git merge di kodingan :
   <<<<<<< HEAD
   // Kode yang ada di branch kamu saat ini
   Kode yang sama muncul disini
   =======
   // Kode dari branch lain yang mau kamu gabungkan
   Ini juga
   >>>>>>> nama branch rekan 

   Tandai kalau Konflik sudah selesai
   git add .
   git commit -m "Resolve merge conflict"
   git push origin NamaBranchKamu

9. Khusus Laravel
   Karena gitignore membiarkan env untuk keamanan jadi file env akan hilang, lakukan ini :
   1. cp .env.example .env (Buat copu env nya)
   2. composer install (Install Composer kalau belum ada)
   3. php artisan key:generate (Untuk membuat Kunci Enkripsi Keamanan)
   4. Atur nama database di file .env baru
   5. php artisan migrate (Generate tabel database)
