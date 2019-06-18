require([
    'jquery'
], function ($) {
    $(document).ready(function () {
        var nav = $('.navigation');
        var subMenus = $(nav).find('ul.submenu');
        $.each(subMenus, function () {
            if (!$(this).hasClass('level0')) {
                var liCount = $(this).find('> li').length;
                if (liCount < 10) {
                    $(this).addClass('line-menu');
                }
            }
        });

        var navBar = $('nav.navigation > ul')[0];
        function menuAdditions() {
            $('li.ui-menu-item').click(function (event) {
                if ($('span.action.nav-toggle').css('display') == 'none') { // only do on desktop menu.
                    event.stopPropagation();
                    $(this).parent().find('li.ui-menu-item a').removeClass('ui-state-active');
                }
            });
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                /*var entry = { // uncomment for debugging.
                    mutation: mutation,
                    el: mutation.target,
                    value: mutation.target.textContent,
                    oldValue: mutation.oldValue
                };
                console.log('Recording mutation:', entry);*/

                menuAdditions();
            });
        });

        var config = {
            attributes: true,
            childList: true,
            characterData: true
        };

        observer.observe(navBar, config);

        window.setTimeout(function () { // disconnect the observer after 8 seconds.
            observer.disconnect();
        }, 8000 );
    });
});