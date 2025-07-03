// assets/js/script.js

// Contoh sederhana: Menampilkan pesan alert setelah beberapa detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Hapus alert setelah 5 detik
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                // Gunakan Bootstrap's dismiss functionality jika tersedia
                $(alert).alert('close'); 
            } else {
                alert.remove();
            }
        }, 5000); // 5000 milidetik = 5 detik
    });

    // Contoh: Konfirmasi sebelum menghapus
    // Ini juga bisa ditangani langsung di atribut onclick HTML seperti di file delete.php
    const deleteButtons = document.querySelectorAll('.btn-danger[onclick*="confirm"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            // Jika Anda ingin mengimplementasikan konfirmasi kustom dengan modal Bootstrap,
            // Anda bisa mencegah default `onclick` di sini dan menampilkan modal.
            // event.preventDefault();
            // $('#confirmDeleteModal').modal('show');
            // Simpan URL dari tombol delete ke modal untuk eksekusi
            // $('#confirmDeleteButton').data('href', this.href);
        });
    });

    // Contoh: Validasi form sederhana sebelum submit (jika tidak menggunakan required HTML5)
    // const form = document.querySelector('form');
    // if (form) {
    //     form.addEventListener('submit', function(event) {
    //         const passwordField = document.getElementById('password');
    //         if (passwordField && passwordField.value.length < 6) {
    //             alert('Password minimal 6 karakter!');
    //             event.preventDefault(); // Mencegah form submit
    //         }
    //     });
    // }
});