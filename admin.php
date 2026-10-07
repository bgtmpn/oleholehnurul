<?php
include 'koneksi.php';

// --- FUNGSI KOMPRES FOTO OTOMATIS ---
function compressAndSaveImage($source, $destination, $quality = 60) {
    $info = getimagesize($source);
    if (!$info) return false;

    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        return false;
    }

    imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return true;
}

// --- 1. PROSES TAMBAH PRODUK ---
if (isset($_POST['tambah'])) {
    $nama_produk = $_POST['nama_produk'];
    $foto_baru   = 'default.jpg';

    if (!empty($_FILES['foto']['tmp_name'])) {
        $temp_file = $_FILES['foto']['tmp_name'];
        $foto_baru = time() . '_' . rand(100, 999) . '.jpg';

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $target_path = 'uploads/' . $foto_baru;
        $success = compressAndSaveImage($temp_file, $target_path, 60);

        if (!$success) {
            move_uploaded_file($temp_file, $target_path);
        }
    }

    $query = "INSERT INTO produk (nama_produk, foto) VALUES ('$nama_produk', '$foto_baru')";
    mysqli_query($koneksi, $query);

    header("Location: admin.php");
    exit();
}

// --- 2. PROSES EDIT PRODUK ---
if (isset($_POST['edit'])) {
    $id          = $_POST['id'];
    $nama_produk = $_POST['nama_produk'];
    $foto_lama   = $_POST['foto_lama'];
    $foto_baru   = $foto_lama;

    // Cek jika ada upload foto baru
    if (!empty($_FILES['foto']['tmp_name'])) {
        $temp_file = $_FILES['foto']['tmp_name'];
        $foto_baru = time() . '_' . rand(100, 999) . '.jpg';

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $target_path = 'uploads/' . $foto_baru;
        $success = compressAndSaveImage($temp_file, $target_path, 60);

        if (!$success) {
            move_uploaded_file($temp_file, $target_path);
        }

        // Hapus foto lama jika bukan default.jpg
        if ($foto_lama != 'default.jpg' && file_exists('uploads/' . $foto_lama)) {
            unlink('uploads/' . $foto_lama);
        }
    }

    $query = "UPDATE produk SET nama_produk='$nama_produk', foto='$foto_baru' WHERE id='$id'";
    mysqli_query($koneksi, $query);

    header("Location: admin.php");
    exit();
}

// --- 3. PROSES HAPUS PRODUK ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $res = mysqli_query($koneksi, "SELECT foto FROM produk WHERE id='$id'");
    $data = mysqli_fetch_assoc($res);
    if ($data['foto'] != 'default.jpg' && file_exists('uploads/' . $data['foto'])) {
        unlink('uploads/' . $data['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM produk WHERE id='$id'");
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aneka Kue Nurul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 sm:p-8">

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Panel Admin Mitra</h1>
                <p class="text-sm text-slate-500">Kelola katalog produk Aneka Kue Nurul</p>
            </div>
            <a href="index.html" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all">
                <i class="fa-solid fa-globe mr-1"></i> Lihat Web
            </a>
        </div>

        <!-- Form Tambah Produk -->
        <div class="bg-white p-6 rounded-2xl shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b pb-2">Tambah Produk Baru</h2>
            <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" required placeholder="Contoh: Kue Lapis Legit" class="w-full px-4 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Foto Produk</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <button type="submit" name="tambah" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-sm transition-all">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Produk
                </button>
            </form>
        </div>

        <!-- Daftar Produk -->
        <div class="bg-white p-6 rounded-2xl shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b pb-2">Daftar Produk saat ini</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-slate-400 text-xs uppercase">
                            <th class="pb-3">Foto</th>
                            <th class="pb-3">Nama Produk</th>
                            <th class="pb-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php
                        $res = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");
                        if (mysqli_num_rows($res) > 0) :
                            while ($row = mysqli_fetch_assoc($res)) :
                        ?>
                        <tr>
                            <td class="py-3">
                                <img src="uploads/<?= $row['foto'] ?>" class="w-12 h-12 object-cover rounded-lg bg-slate-100" onerror="this.src='https://via.placeholder.com/100?text=No+Img'">
                            </td>
                            <td class="py-3 font-semibold text-slate-800"><?= htmlspecialchars($row['nama_produk']) ?></td>
                            <td class="py-3 text-center space-x-1">
                                <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_produk'])) ?>', '<?= $row['foto'] ?>')" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <a href="admin.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin mau hapus produk ini?')" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-400 text-xs">Belum ada produk. Silakan tambah produk di atas.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PRODUK -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full space-y-4 shadow-xl">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-slate-900">Edit Produk</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="foto_lama" id="edit_foto_lama">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" id="edit_nama" required class="w-full px-4 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Ganti Foto (Biarkan kosong jika tidak diubah)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeEditModal()" class="w-1/2 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl">Batal</button>
                    <button type="submit" name="edit" class="w-1/2 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nama, foto) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_foto_lama').value = foto;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>