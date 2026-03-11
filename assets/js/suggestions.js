jQuery(document).ready(function($) {
    function initSuggestions(inputSelector, suggestionsData) {
        const $input = $(inputSelector);
        const $wrapper = $input.parent();
        
        // Create suggestions container if not exists
        if (!$wrapper.find('.tmreviews-suggestions').length) {
            $wrapper.append('<div class="tmreviews-suggestions"></div>');
        }
        
        const $suggestions = $wrapper.find('.tmreviews-suggestions');

        // Показываем все подсказки
        function showAllSuggestions() {
            if (suggestionsData.length > 0) {
                $suggestions.html(suggestionsData.map(item => 
                    `<div class="tmreviews-suggestions-item">${item}</div>`
                ).join('')).addClass('active');
            }
        }

        // Фильтруем подсказки по введенному тексту
        function filterSuggestions(value) {
            if (!value) {
                showAllSuggestions();
                return;
            }

            const matches = suggestionsData.filter(item => 
                item.toLowerCase().includes(value.toLowerCase())
            );
            
            if (matches.length > 0) {
                $suggestions.html(matches.map(item => 
                    `<div class="tmreviews-suggestions-item">${item}</div>`
                ).join('')).addClass('active');
            } else {
                $suggestions.removeClass('active');
            }
        }
        
        // При фокусе показываем все подсказки
        $input.on('focus', showAllSuggestions);
        
        // При вводе фильтруем подсказки
        $input.on('input', function() {
            filterSuggestions($(this).val());
        });
        
        // При клике на подсказку
        $suggestions.on('click', '.tmreviews-suggestions-item', function() {
            $input.val($(this).text());
            $suggestions.removeClass('active');
        });
        
        // Закрываем подсказки при клике вне
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.tmreviews-input-wrapper').length) {
                $suggestions.removeClass('active');
            }
        });
    }
    
    // Initialize for pros and cons fields
    if (typeof tmreviewsSuggestions !== 'undefined') {
        if (tmreviewsSuggestions.pros) {
            initSuggestions('.tmreviews-pros-input', tmreviewsSuggestions.pros);
        }
        if (tmreviewsSuggestions.cons) {
            initSuggestions('.tmreviews-cons-input', tmreviewsSuggestions.cons);
        }
    }
});
