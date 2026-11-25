// ==========================================
// GYMFLOW — GERENCIADOR DE MÍDIAS
// assets/js/exercicio-midia.js
// ==========================================

(() => {

    'use strict';

    function iniciarGerenciador() {

        const form = document.getElementById('formExercicio');
        const manager = document.getElementById('mediaManager');

        if (!form || !manager) return;

        const elementos = {
            arquivo: document.getElementById('arquivo'),
            original: document.getElementById('mediaOriginal'),
            preview: document.getElementById('mediaPreview'),
            vazio: document.getElementById('mediaEmpty'),
            vazioTitulo: document.getElementById('mediaEmptyTitle'),
            vazioTexto: document.getElementById('mediaEmptyText'),

            status: document.getElementById('mediaStatus'),
            formato: document.getElementById('mediaFormat'),

            informacoes: document.getElementById('mediaFileInfo'),
            nome: document.getElementById('mediaFileName'),
            tamanho: document.getElementById('mediaFileSize'),

            escolher: document.getElementById('mediaChoose'),
            escolherTexto: document.getElementById('mediaChooseText'),
            limpar: document.getElementById('mediaClear'),

            remover: document.getElementById('remover_midia'),
            removerOpcao: document.getElementById('mediaRemoveOption'),
            removerTexto: document.getElementById('mediaRemoveText'),

            erro: document.getElementById('mediaError'),
            aviso: document.getElementById('mediaNoticeText')
        };

        const possuiOriginal =
            manager.dataset.possuiMidia === '1';

        const descricaoOriginal =
            manager.dataset.descricaoOriginal || 'Mídia';

        // ======================================
        // 1. FORMATOS ACEITOS
        // ======================================

        const formatos = Object.freeze({

            jpg: {
                tipo: 'imagem',
                descricao: 'Imagem JPG',
                limite: 5
            },

            jpeg: {
                tipo: 'imagem',
                descricao: 'Imagem JPEG',
                limite: 5
            },

            png: {
                tipo: 'imagem',
                descricao: 'Imagem PNG',
                limite: 5
            },

            webp: {
                tipo: 'imagem',
                descricao: 'Imagem WEBP',
                limite: 5
            },

            gif: {
                tipo: 'imagem',
                descricao: 'GIF animado',
                limite: 15
            },

            mp4: {
                tipo: 'video',
                descricao: 'Vídeo MP4',
                limite: 50
            },

            webm: {
                tipo: 'video',
                descricao: 'Vídeo WEBM',
                limite: 50
            }

        });

        // ======================================
        // 2. ESTADO CENTRAL
        // ======================================

        const estado = {

            arquivo: null,
            config: null,

            urlTemporaria: null,
            elementoPreview: null,

            carregando: false,

            erro: '',

            originalIndisponivel: false

        };

        // ======================================
        // 3. FUNÇÕES AUXILIARES
        // ======================================

        function extensaoDoArquivo(arquivo) {

            const nome = arquivo.name;

            const posicao = nome.lastIndexOf('.');

            return posicao >= 0
                ? nome.slice(posicao + 1).toLowerCase()
                : '';
        }

        function formatarTamanho(bytes) {

            const mb = bytes / (1024 * 1024);

            if (mb < 1) {

                return new Intl.NumberFormat('pt-BR', {
                    maximumFractionDigits: 1
                }).format(bytes / 1024) + ' KB';
            }

            return new Intl.NumberFormat('pt-BR', {
                maximumFractionDigits: 2
            }).format(mb) + ' MB';
        }

        function pararVideo(container) {

            if (!container) return;

            const video = container.querySelector('video');

            if (video) video.pause();
        }

        function definirTexto(elemento, texto) {

            if (elemento && elemento.textContent !== texto) {

                elemento.textContent = texto;
            }
        }

        function remocaoSolicitada() {

            return Boolean(
                elementos.remover &&
                elementos.remover.checked
            );
        }

        function mostrarVazio(titulo, descricao) {

            elementos.vazio.hidden = false;

            definirTexto(elementos.vazioTitulo, titulo);

            definirTexto(elementos.vazioTexto, descricao);
        }

        // ======================================
        // 4. LIBERAR PRÉVIA ANTERIOR
        // ======================================

        function liberarPreview() {

            const elementoAnterior = estado.elementoPreview;

            // Invalida eventos antigos de carregamento.

            estado.elementoPreview = null;

            elementos.preview.replaceChildren();

            if (
                elementoAnterior &&
                elementoAnterior.tagName === 'VIDEO'
            ) {

                elementoAnterior.pause();
                elementoAnterior.removeAttribute('src');
                elementoAnterior.load();
            }

            if (estado.urlTemporaria) {

                URL.revokeObjectURL(estado.urlTemporaria);

                estado.urlTemporaria = null;
            }

            estado.carregando = false;
        }

        // ======================================
        // 5. VALIDAÇÃO
        // ======================================

        function validarArquivo(arquivo) {

            const extensao = extensaoDoArquivo(arquivo);

            if (!Object.hasOwn(formatos, extensao)) {

                return {
                    erro: 'Formato não suportado. Utilize JPG, JPEG, PNG, WEBP, GIF, MP4 ou WEBM.'
                };
            }

            const config = formatos[extensao];

            if (arquivo.size <= 0) {

                return {
                    erro: 'O arquivo selecionado está vazio.'
                };
            }

            const limiteBytes =
                config.limite * 1024 * 1024;

            if (arquivo.size > limiteBytes) {

                return {
                    erro:
                        `O arquivo possui ${formatarTamanho(arquivo.size)}. ` +
                        `O limite para este formato é ${config.limite} MB.`
                };
            }

            return { config };
        }

        // ======================================
        // 6. DETERMINAR ESTADO DA INTERFACE
        // ======================================

        function obterEstadoVisual() {

            if (estado.arquivo) {

                if (estado.erro) return 'invalida';

                if (estado.carregando) return 'carregando';

                return 'nova';
            }

            if (remocaoSolicitada()) return 'removida';

            if (possuiOriginal) {

                return estado.originalIndisponivel
                    ? 'indisponivel'
                    : 'atual';
            }

            return 'vazio';
        }

        // ======================================
        // 7. RENDERIZAÇÃO CENTRAL
        // ======================================

        function renderizar() {

            const situacao = obterEstadoVisual();

            const temNovoArquivo =
                estado.arquivo !== null;

            manager.dataset.state = situacao;

            // Oculta todas as camadas inicialmente.

            if (elementos.original) {
                elementos.original.hidden = true;
            }

            elementos.preview.hidden = true;
            elementos.vazio.hidden = true;

            if (situacao !== 'atual') {
                pararVideo(elementos.original);
            }

            // As informações e o botão de cancelar
            // aparecem inclusive para arquivos inválidos.

            elementos.informacoes.hidden = !temNovoArquivo;

            definirTexto(
                elementos.nome,
                temNovoArquivo ? estado.arquivo.name : ''
            );

            definirTexto(
                elementos.tamanho,
                temNovoArquivo
                    ? formatarTamanho(estado.arquivo.size)
                    : ''
            );

            // Não envia a solicitação de remoção quando
            // existe um novo arquivo selecionado.
            //
            // O valor anterior fica preservado para
            // retornar a ele se a seleção for cancelada.

            if (elementos.remover) {

                elementos.remover.disabled = temNovoArquivo;

                elementos.removerOpcao.hidden = temNovoArquivo;

                definirTexto(
                    elementos.removerTexto,
                    remocaoSolicitada()
                        ? 'Desfazer remoção'
                        : 'Remover mídia'
                );
            }

            elementos.erro.hidden = !estado.erro;

            definirTexto(
                elementos.erro,
                estado.erro
            );

            definirTexto(
                elementos.formato,
                estado.config
                    ? estado.config.descricao
                    : ''
            );

            // ==================================
            // ESTADOS
            // ==================================

            switch (situacao) {

                case 'atual':

                    elementos.original.hidden = false;

                    definirTexto(
                        elementos.status,
                        'Mídia cadastrada'
                    );

                    definirTexto(
                        elementos.formato,
                        descricaoOriginal
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Substituir mídia'
                    );

                    definirTexto(
                        elementos.aviso,
                        'A mídia cadastrada será mantida se nenhuma alteração for realizada.'
                    );

                    break;

                case 'nova':

                    elementos.preview.hidden = false;

                    definirTexto(
                        elementos.status,
                        'Nova mídia selecionada'
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Escolher outro arquivo'
                    );

                    definirTexto(
                        elementos.aviso,
                        possuiOriginal
                            ? 'A nova demonstração substituirá a mídia cadastrada ao salvar.'
                            : 'A demonstração será adicionada ao exercício ao salvar.'
                    );

                    break;

                case 'carregando':

                    // Exibe a camada enquanto prepara a mídia.

                    elementos.preview.hidden = false;

                    definirTexto(
                        elementos.status,
                        'Carregando demonstração...'
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Escolher outro arquivo'
                    );

                    definirTexto(
                        elementos.aviso,
                        'Aguarde o carregamento da prévia.'
                    );

                    break;

                case 'removida':

                    mostrarVazio(
                        'Mídia marcada para remoção',
                        'O exercício continuará disponível na biblioteca.'
                    );

                    definirTexto(
                        elementos.status,
                        'Remoção pendente'
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Adicionar nova mídia'
                    );

                    definirTexto(
                        elementos.aviso,
                        'A remoção será confirmada ao salvar. Você pode desfazer esta ação.'
                    );

                    break;

                case 'invalida':

                    mostrarVazio(
                        'Arquivo incompatível',
                        'Escolha outro arquivo ou cancele a seleção.'
                    );

                    definirTexto(
                        elementos.status,
                        'Não foi possível carregar'
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Escolher outro arquivo'
                    );

                    definirTexto(
                        elementos.aviso,
                        'Nenhuma alteração foi aplicada à mídia cadastrada.'
                    );

                    break;

                case 'indisponivel':

                    mostrarVazio(
                        'Mídia indisponível',
                        'O arquivo cadastrado não pôde ser carregado.'
                    );

                    definirTexto(
                        elementos.status,
                        'Arquivo indisponível'
                    );

                    definirTexto(
                        elementos.formato,
                        descricaoOriginal
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Substituir mídia'
                    );

                    definirTexto(
                        elementos.aviso,
                        'Você pode substituir ou remover a referência da mídia atual.'
                    );

                    break;

                default:

                    mostrarVazio(
                        'Nenhuma demonstração',
                        'Adicione uma foto, GIF ou vídeo quando desejar.'
                    );

                    definirTexto(
                        elementos.status,
                        'Sem mídia'
                    );

                    definirTexto(
                        elementos.escolherTexto,
                        'Adicionar mídia'
                    );

                    definirTexto(
                        elementos.aviso,
                        'A demonstração é opcional. Você pode salvar o exercício sem mídia.'
                    );

                    break;
            }
        }

        // ======================================
        // 8. PREPARAR NOVO ARQUIVO
        // ======================================

        function selecionarArquivo() {

            liberarPreview();

            estado.arquivo =
                elementos.arquivo.files[0] || null;

            estado.config = null;
            estado.erro = '';

            if (!estado.arquivo) {

                renderizar();

                return;
            }

            const resultado =
                validarArquivo(estado.arquivo);

            if (resultado.erro) {

                estado.erro = resultado.erro;

                renderizar();

                return;
            }

            estado.config = resultado.config;

            estado.carregando = true;

            try {

                estado.urlTemporaria =
                    URL.createObjectURL(estado.arquivo);

                const elemento = document.createElement(
                    estado.config.tipo === 'video'
                        ? 'video'
                        : 'img'
                );

                estado.elementoPreview = elemento;

                if (estado.config.tipo === 'video') {

                    elemento.controls = true;
                    elemento.playsInline = true;
                    elemento.preload = 'metadata';

                } else {

                    elemento.alt =
                        'Prévia da demonstração selecionada';
                }

                const eventoCarregamento =
                    estado.config.tipo === 'video'
                        ? 'loadedmetadata'
                        : 'load';

                elemento.addEventListener(
                    eventoCarregamento,
                    () => {

                        if (
                            estado.elementoPreview !== elemento
                        ) return;

                        estado.carregando = false;

                        renderizar();
                    },
                    { once: true }
                );

                elemento.addEventListener('error', () => {

                    if (
                        estado.elementoPreview !== elemento
                    ) return;

                    liberarPreview();

                    estado.erro =
                        'O navegador não conseguiu reproduzir este arquivo. Selecione outro formato ou arquivo.';

                    renderizar();
                });

                elementos.preview.appendChild(elemento);

                renderizar();

                elemento.src = estado.urlTemporaria;

            } catch {

                liberarPreview();

                estado.erro =
                    'Não foi possível gerar a prévia do arquivo.';

                renderizar();
            }
        }

        // ======================================
        // 9. CANCELAR NOVA SELEÇÃO
        // ======================================

        function cancelarSelecao() {

            elementos.arquivo.value = '';

            liberarPreview();

            estado.arquivo = null;
            estado.config = null;
            estado.erro = '';

            // Retorna à mídia anterior ou à remoção
            // que já estava solicitada antes da seleção.

            renderizar();

            elementos.escolher.focus();
        }

        // ======================================
        // 10. VERIFICAR MÍDIA CADASTRADA
        // ======================================

        if (elementos.original) {

            const midiaOriginal =
                elementos.original.querySelector('img, video');

            if (midiaOriginal) {

                const marcarIndisponivel = () => {

                    estado.originalIndisponivel = true;

                    renderizar();
                };

                midiaOriginal.addEventListener(
                    'error',
                    marcarIndisponivel
                );

                const imagemQuebrada =
                    midiaOriginal.tagName === 'IMG' &&
                    midiaOriginal.complete &&
                    midiaOriginal.naturalWidth === 0;

                if (imagemQuebrada || midiaOriginal.error) {

                    estado.originalIndisponivel = true;
                }
            }
        }

        // ======================================
        // 11. EVENTOS
        // ======================================

        elementos.escolher.addEventListener('click', () => {

            elementos.arquivo.click();
        });

        elementos.arquivo.addEventListener(
            'change',
            selecionarArquivo
        );

        elementos.limpar.addEventListener(
            'click',
            cancelarSelecao
        );

        if (elementos.remover) {

            elementos.remover.addEventListener('change', () => {

                renderizar();
            });
        }

        // ======================================
        // 12. VALIDAÇÃO ANTES DO ENVIO
        // ======================================

        form.addEventListener('submit', event => {

            if (estado.erro) {

                event.preventDefault();

                elementos.escolher.focus();

                return;
            }

            if (estado.carregando) {

                event.preventDefault();

                elementos.erro.hidden = false;

                definirTexto(
                    elementos.erro,
                    'Aguarde o carregamento da demonstração antes de salvar.'
                );

                return;
            }

            // Confirma a remoção somente no momento
            // em que o usuário salva o exercício.


        });

        // ======================================
        // 13. LIBERAR RECURSOS
        // ======================================

        window.addEventListener('pagehide', event => {

            if (!event.persisted) {

                liberarPreview();
            }
        });

        // ======================================
        // 14. INICIALIZAÇÃO
        // ======================================

        manager.dataset.enhanced = 'true';

        elementos.escolher.hidden = false;

        if (elementos.arquivo.files.length > 0) {

            selecionarArquivo();

        } else {

            renderizar();
        }
    }

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            iniciarGerenciador
        );

    } else {

        iniciarGerenciador();
    }

})();