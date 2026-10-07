<?php
header('Content-Type: application/json');
include 'koneksi.php';

$query = "SELECT * FROM produk ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);

$produk_list = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $produk_list[] = array(
            "id" => (int)$row['id'],
            "name" => $row['nama_produk'],
            "image" => "uploads/" . $row['foto'],
            "shopee" => $row['link_shopee']
        );
    }
}

echo json_encode($produk_list);
?>