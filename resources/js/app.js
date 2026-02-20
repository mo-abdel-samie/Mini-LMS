import "./bootstrap";
import "plyr/dist/plyr.css";
import Plyr from "plyr";
import Alpine from "alpinejs";


const THEME_STORAGE_KEY = "theme_mode";
const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
const initialDark = savedTheme ? savedTheme === "dark" : prefersDark;

document.documentElement.classList.toggle("dark", initialDark);
localStorage.setItem(THEME_STORAGE_KEY, initialDark ? "dark" : "light");

Alpine.store("themeMode", {
    on: initialDark,

    toggle() {
        this.on = !this.on;
        document.documentElement.classList.toggle("dark", this.on);
        localStorage.setItem(THEME_STORAGE_KEY, this.on ? "dark" : "light");
    },
});
Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    const players = document.querySelectorAll(".js-plyr");

    if (players.length === 0) {
        return;
    }

    players.forEach((playerElement) => {
        const player = new Plyr(playerElement);

        window.dispatchEvent(
            new CustomEvent("plyr:ready", {
                detail: {
                    element: playerElement,
                    player,
                },
            }),
        );
    });
});
