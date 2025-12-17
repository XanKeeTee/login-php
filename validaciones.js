document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");
    const usuarioInput = document.getElementById("usuario");
    const passwordInput = document.getElementById("password");
    
    const errorUsuario = document.getElementById("errorUsuario");
    const errorPassword = document.getElementById("errorPassword");

    form.addEventListener("submit", function(e) {
        let esValido = true;

        errorUsuario.textContent = "";
        errorPassword.textContent = "";
        usuarioInput.classList.remove("is-invalid");
        passwordInput.classList.remove("is-invalid");

        const usuario = usuarioInput.value.trim();
        
        if (usuario.length < 8 || usuario.length > 15) {
            mostrarError(usuarioInput, errorUsuario, "El usuario debe tener entre 8 y 15 caracteres.");
            esValido = false;
        } 
        else if (!/^[a-zA-Z0-9]+$/.test(usuario)) {
            mostrarError(usuarioInput, errorUsuario, "El usuario solo puede contener letras y números.");
            esValido = false;
        }

        const password = passwordInput.value;

        const caracteresSeguros = /^[a-zA-Z0-9@#$%\*_\-\+\.]+$/;
        
        const tieneMayuscula = /[A-Z]/.test(password);
        const tieneMinuscula = /[a-z]/.test(password);
        const tieneEspecial = /[@#$%\*_\-\+\.]/.test(password);

        if (password.length < 8 || password.length > 15) {
            mostrarError(passwordInput, errorPassword, "La contraseña debe tener entre 8 y 15 caracteres.");
            esValido = false;
        } 
        else if (!caracteresSeguros.test(password)) {
            mostrarError(passwordInput, errorPassword, "La contraseña contiene caracteres prohibidos. Solo se permiten letras, números y: @ # $ % * _ - + .");
            esValido = false;
        } 
        else if (!tieneMayuscula || !tieneMinuscula || !tieneEspecial) {
            mostrarError(passwordInput, errorPassword, "La contraseña debe incluir al menos una mayúscula, una minúscula y un carácter especial.");
            esValido = false;
        }

        if (!esValido) {
            e.preventDefault();
        }
    });

    function mostrarError(input, divError, mensaje) {
        input.classList.add("is-invalid");
        divError.textContent = mensaje;
    }
});