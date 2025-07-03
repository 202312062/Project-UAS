<?php
// pages/contact.php

session_start();
include_once '../includes/header.php';
?>

<div class="container container-main">
    <h1 class="my-4 text-center">Hubungi Kami</h1>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="lead text-center">Jika Anda memiliki pertanyaan, saran, atau masalah, jangan ragu untuk menghubungi kami melalui formulir di bawah ini atau detail kontak kami.</p>

                    <h5 class="mt-4">Informasi Kontak Kami:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> Alamat: Jl. Contoh Elektronik No. 123, Kota Teknologi, 12345</li>
                        <li><i class="fas fa-phone"></i> Telepon: (021) 1234 5678</li>
                        <li><i class="fas fa-envelope"></i> Email: info@egadgetstore.com</li>
                        <li><i class="fas fa-clock"></i> Jam Kerja: Senin - Jumat, 09:00 - 17:00 WITA</li>
                    </ul>

                    <h5 class="mt-4">Kirim Pesan kepada Kami:</h5>
                    <form action="submit_contact.php" method="POST">
                        <div class="form-group">
                            <label for="name">Nama Anda</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Anda</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subjek</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Pesan Anda</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>