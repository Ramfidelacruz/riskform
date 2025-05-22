document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('riskmanagers-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value);

            try {
                const response = await fetch('/wp-json/riskmanagers/v1/enviar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if(result.status === 'success') {
                    alert('Formulario enviado correctamente');
                    form.reset();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Ocurrió un error al enviar el formulario');
            }
        });
    }
}); 