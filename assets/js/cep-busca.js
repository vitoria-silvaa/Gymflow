// assets/js/cep-busca.js
function buscarCep() {
    var inputCep = document.getElementById('cep');
    var inputEndereco = document.getElementById('endereco');
    if (!inputCep || !inputEndereco) {
        return;
    }

    var cep = inputCep.value.replace(/\D/g, '');
    if (cep === '') {
        return;
    }

    if (/^[0-9]{8}$/.test(cep)) {
        inputEndereco.value = '...';
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!('erro' in data)) {
                    inputEndereco.value = data.logradouro + ',  - ' + data.bairro + ', ' + data.localidade + ' - ' + data.uf;
                } else {
                    alert('CEP não encontrado.');
                    inputEndereco.value = '';
                }
            })
            .catch(function () {
                alert('Erro ao buscar CEP.');
                inputEndereco.value = '';
            });
    } else {
        alert('Formato de CEP inválido.');
    }
}
