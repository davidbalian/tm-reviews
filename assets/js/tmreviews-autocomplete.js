jQuery(document).ready(function($) {
    // Обработка клика по подсказкам
    function handleSuggestionClick($input, $suggestions) {
        $suggestions.find('li').on('click', function() {
            var value = $(this).data('value');
            $input.val(value);
            $suggestions.find('li').removeClass('active');
            $(this).addClass('active');
        });
    }

    // Подсветка подходящих вариантов
    function filterSuggestions($input, $suggestions) {
        var value = $input.val().toLowerCase();
        var hasVisibleItems = false;

        $suggestions.find('li').each(function() {
            var text = $(this).text().toLowerCase();
            var isMatch = text.indexOf(value) > -1;
            $(this).toggle(isMatch);
            if (isMatch) hasVisibleItems = true;
        });

        // Показываем сообщение, если нет совпадений
        var $noResults = $suggestions.find('.no-results');
        if (!hasVisibleItems) {
            if (!$noResults.length) {
                $suggestions.find('ul').append('<li class="no-results">' + tmreviews_autocomplete.no_results + '</li>');
            }
        } else {
            $noResults.remove();
        }
    }

    // Инициализация для pros
    handleSuggestionClick($('#fl-pros'), $('.pros-suggestions'));
    $('#fl-pros').on('input', function() {
        filterSuggestions($(this), $('.pros-suggestions'));
    });

    // Инициализация для cons
    handleSuggestionClick($('#fl-cons'), $('.cons-suggestions'));
    $('#fl-cons').on('input', function() {
        filterSuggestions($(this), $('.cons-suggestions'));
    });

    // Навигация по клавиатуре
    $('.fl-pros, .fl-cons').on('keydown', function(e) {
        var $suggestions = $(this).hasClass('fl-pros') ? $('.pros-suggestions') : $('.cons-suggestions');
        var $items = $suggestions.find('li:visible:not(.no-results)');
        var $active = $suggestions.find('li.active:visible');
        var index = $items.index($active);

        switch(e.keyCode) {
            case 38: // Up
                e.preventDefault();
                if ($active.length) {
                    index = Math.max(0, index - 1);
                } else {
                    index = $items.length - 1;
                }
                break;
            case 40: // Down
                e.preventDefault();
                if ($active.length) {
                    index = Math.min($items.length - 1, index + 1);
                } else {
                    index = 0;
                }
                break;
            case 13: // Enter
                e.preventDefault();
                if ($active.length) {
                    $active.click();
                }
                return;
        }

        $items.removeClass('active');
        if ($items.length) {
            $($items[index]).addClass('active');
            // Прокручиваем к выбранному элементу
            var $container = $suggestions.find('ul');
            var itemTop = $($items[index]).position().top;
            var containerHeight = $container.height();
            var scrollTop = $container.scrollTop();

            if (itemTop < 0) {
                $container.scrollTop(scrollTop + itemTop);
            } else if (itemTop + $($items[index]).height() > containerHeight) {
                $container.scrollTop(scrollTop + itemTop - containerHeight + $($items[index]).height());
            }
        }
    });
});

