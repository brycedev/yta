import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
    ],

    theme: {
        extend: {
            colors: {
                audius: {
                    50: "#f4f1fc",
                    100: "#f8f1fd",
                    200: "#dab7f5",
                    300: "#bd7cee",
                    400: "#9f42e6",
                    500: "#7e1bcb",
                    600: "#6515a2",
                    700: "#4f117e",
                    800: "#350b56",
                    900: "#1c062d",
                },
            },
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
