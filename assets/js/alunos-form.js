// assets/js/alunos-form.js

function toggleCondominio() {
    var checkbox = document.getElementById('is_condominio');
    var container = document.getElementById('div_condominio');
    if (!checkbox || !container) {
        return;
    }

    container.classList.toggle('visivel', checkbox.checked);
    atualizarEndereco();
}

function atualizarEndereco() {
    var rua = document.getElementById('rua').value;
    var numero = document.getElementById('numero').value;
    var bairro = document.getElementById('bairro').value;
    var cidade = document.getElementById('cidade_uf').value;
    var condominio = document.getElementById('is_condominio').checked;
    var bloco = document.getElementById('bloco').value;
    var apto = document.getElementById('apto').value;

    var partes = [];

    if (rua !== '') {
        if (numero !== '') {
            partes.push(rua + ', ' + numero);
        } else {
            partes.push(rua);
        }
    }

    if (condominio) {
        if (bloco !== '') {
            partes.push('Bloco ' + bloco);
        }
        if (apto !== '') {
            partes.push('Apto ' + apto);
        }
    }

    if (bairro !== '') {
        partes.push(bairro);
    }

    if (cidade !== '') {
        partes.push(cidade);
    }

    if (partes.length > 0) {
        document.getElementById('endereco').value = partes.join(' - ');
    }
}

function buscarCep(cep) {
    cep = cep.replace(/\D/g, '');

    if (cep.length !== 8) {
        return;
    }

    var campoEndereco = document.getElementById('endereco');
    campoEndereco.value = 'Buscando CEP...';

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (resultado) {
            if (resultado.erro) {
                alert('CEP não encontrado!');
                campoEndereco.value = '';
                return;
            }

            document.getElementById('rua').value = resultado.logradouro || '';
            document.getElementById('bairro').value = resultado.bairro || '';
            document.getElementById('cidade_uf').value = (resultado.localidade || '') + '/' + (resultado.uf || '');

            atualizarEndereco();
        })
        .catch(function () {
            alert('Erro ao buscar o CEP.');
            campoEndereco.value = '';
        });
}
