tailwind.config = {
  theme: {
    extend: {
      colors: {
        sena: {
          50: '#ecfdf5',
          100: '#d1fae5',
          500: '#10b981',
          600: '#008F39', // Official SENA Green
          700: '#00752e',
          800: '#005a23',
          900: '#00421a'
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
      }
    }
  }
}

function err(mess = "") {
    if (mess) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            html: "<strong>Error:</strong> ¡" + mess + "!",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}
