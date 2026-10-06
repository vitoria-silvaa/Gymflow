document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('appSidebar');
    const toggleButton = document.getElementById('sidebarToggle');

    if (!sidebar || !toggleButton) {
        return;
    }


    /*
     * Recupera o estado salvo
     */
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');


    /*
     * Aplica o estado salvo somente no desktop
     */
    if (
        sidebarCollapsed === 'true' &&
        window.innerWidth > 768
    ) {
        document.body.classList.add('sidebar-collapsed');
    }


    /*
     * Botão de abrir/recolher
     */
    toggleButton.addEventListener('click', function () {

        if (window.innerWidth <= 768) {

            sidebar.classList.toggle('open');

            return;
        }


        /*
         * Desktop
         */
        document.body.classList.toggle('sidebar-collapsed');


        /*
         * Salva preferência
         */
        const isCollapsed =
            document.body.classList.contains('sidebar-collapsed');

        localStorage.setItem(
            'sidebarCollapsed',
            isCollapsed
        );

    });


    /*
     * Ao redimensionar a tela
     */
    window.addEventListener('resize', function () {

        if (window.innerWidth > 768) {

            sidebar.classList.remove('open');

            const savedState =
                localStorage.getItem('sidebarCollapsed');

            if (savedState === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

        }

    });

});