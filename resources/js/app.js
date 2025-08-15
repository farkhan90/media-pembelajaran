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

// =======================================================
//          LOGIKA ANIMASI UNTUK PETA PETUALANGAN
// =======================================================
document.addEventListener("alpine:init", () => {
    Alpine.data("floatingAnimations", () => ({
        // State baru untuk melacak pesawat
        activePlanes: 0,
        maxPlanes: 2, // Batas maksimal pesawat
        init() {
            const cloudElements = gsap.utils.toArray(".floating-element");
            cloudElements.forEach((el) => this.animateCloud(el));

            const planeElements = gsap.utils.toArray(".plane-element");
            planeElements.forEach((el) => {
                // Setiap pesawat akan mencoba terbang secara independen
                // Beri delay awal acak agar tidak semua mencoba terbang bersamaan
                gsap.delayedCall(gsap.utils.random(0, 5), () =>
                    this.tryToAnimatePlane(el)
                );
            });
        },

        animateCloud(el) {
            // Tetap gunakan gsap.utils.random untuk rentang numerik
            const random = gsap.utils.random;
            const isFrontCloud = el.alt.includes("Depan");

            // =======================================================
            //          PERBAIKAN UTAMA: GUNAKAN Math.random()
            // =======================================================
            // Gunakan Math.random() untuk keputusan biner (true/false) yang andal
            const fromLeft = Math.random() < 0.5;
            // =======================================================

            // Tentukan titik awal dan akhir berdasarkan keputusan di atas
            const startX = fromLeft ? -200 : window.innerWidth + 200;
            const endX = fromLeft ? window.innerWidth + 200 : -200;

            // Parameter acak lainnya tetap menggunakan GSAP random
            const startY = random(
                0,
                window.innerHeight * (isFrontCloud ? 0.8 : 0.6)
            );
            const scale = random(
                isFrontCloud ? 0.15 : 0.8,
                isFrontCloud ? 0.4 : 1.5
            );
            const opacity = random(
                isFrontCloud ? 0.6 : 0.4,
                isFrontCloud ? 0.9 : 0.8
            );
            const duration = random(
                isFrontCloud ? 20 : 40,
                isFrontCloud ? 40 : 80
            );

            const tl = gsap.timeline({
                onComplete: () => {
                    this.animateCloud(el);
                },
            });

            tl.set(el, {
                x: startX,
                y: startY,
                scale: scale,
                opacity: opacity,
            });

            tl.to(el, {
                x: endX,
                duration: duration,
                ease: "none",
            });
        },

        tryToAnimatePlane(el) {
            const random = gsap.utils.random;

            // Cek apakah ada slot terbang yang tersedia
            if (this.activePlanes < this.maxPlanes) {
                // Jika ya, ambil slot dan mulai terbang
                this.activePlanes++;
                this.animatePlane(el);
            } else {
                gsap.delayedCall(random(5, 10), () =>
                    this.tryToAnimatePlane(el)
                );
            }
        },

        animatePlane(el) {
            // Gunakan Math.random() untuk keputusan biner (true/false)
            const fromLeft = Math.random() < 0.5;

            // Tetap gunakan utilitas GSAP untuk rentang numerik
            const random = gsap.utils.random;

            let startX, endX, scaleX;

            if (fromLeft) {
                // Parameter untuk terbang dari KIRI ke KANAN
                startX = -200;
                endX = window.innerWidth + 200;
                scaleX = 1;
            } else {
                // Parameter untuk terbang dari KANAN ke KIRI
                startX = window.innerWidth + 200;
                endX = -200;
                scaleX = -1;
            }

            const startY = random(
                window.innerHeight * 0.1,
                window.innerHeight * 0.5
            );
            const endY = random(
                window.innerHeight * 0.1,
                window.innerHeight * 0.5
            );
            const startRotation = random(-5, 10);
            const endRotation = random(-10, 5);

            const tl = gsap.timeline({
                onComplete: () => {
                    this.activePlanes--;
                    gsap.delayedCall(random(5, 15), () =>
                        this.tryToAnimatePlane(el)
                    );
                },
            });

            tl.set(el, {
                x: startX,
                y: startY,
                rotation: startRotation,
                scaleX: scaleX,
                opacity: 1,
            });

            tl.to(el, {
                x: endX,
                y: endY,
                rotation: endRotation,
                duration: random(10, 18),
                ease: "power1.inOut",
            });
        },
    }));
});
