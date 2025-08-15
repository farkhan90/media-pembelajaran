import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import Chart from "chart.js/auto";
window.Chart = Chart;

import SplitType from "split-type"; // <-- Impor dari paket npm
window.SplitType = SplitType; // <-- Daftarkan ke window
