// resources/js/app.js

import "./bootstrap";

// Import GSAP (jika Anda menggunakannya)
import { gsap } from "gsap";
window.gsap = gsap;

// Import SweetAlert2
import Swal from "sweetalert2";
window.Swal = Swal;

// Listener event utama Livewire
document.addEventListener("livewire:init", () => {
    // Listener untuk notifikasi swal sederhana (untuk create/update)
    Livewire.on("swal", (event) => {
        // Cek apakah data event ada dan merupakan array
        if (event && Array.isArray(event) && event.length > 0) {
            Swal.fire({
                title: event[0].title,
                text: event[0].text,
                icon: event[0].icon,
            });
        }
    });

    // Listener untuk konfirmasi swal (untuk hapus data)
    Livewire.on("swal:confirm", (event) => {
        console.log(event);
        Swal.fire({
            title: event.title || "Apakah Anda yakin?",
            text: event.text || "",
            icon: event.icon || "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: event.confirmButtonText || "Ya, Lanjutkan!",
            cancelButtonText: event.cancelButtonText || "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Pastikan objek 'next' dan 'event' di dalamnya ada sebelum dispatch
                Livewire.dispatch(event.next.event, event.next.params || {});
            }
        });
    });
});

window.addEventListener("ujian-telah-selesai", (event) => {
    Swal.fire({
        title: event.detail[0].title,
        text: event.detail[0].text,
        icon: event.detail[0].icon,
        // Konfigurasi tambahan
        allowOutsideClick: true, // Izinkan klik di luar untuk menutup
        confirmButtonText: "OK",
    }).then((result) => {
        // Redirect akan terjadi setelah dialog ditutup,
        // baik dengan klik OK, tombol Esc, atau klik di luar.
        window.location.href = event.detail[0].redirectUrl;
    });
});

// resources/js/app.js

// ... (listener swal, swal:confirm, dan ujian-telah-selesai yang sudah ada) ...

// LISTENER BARU UNTUK KUIS SELESAI
window.addEventListener("kuis-telah-selesai", (event) => {
    Swal.fire({
        title: event.detail[0].title,
        text: event.detail[0].text,
        icon: event.detail[0].icon,
        allowOutsideClick: true,
        confirmButtonText: "OK",
    }).then(() => {
        // Redirect setelah dialog ditutup
        window.location.href = event.detail[0].redirectUrl;
    });
});
