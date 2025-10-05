document.addEventListener('DOMContentLoaded', function () {
    initializeLiveSearch('live-search-input', 'live-search-results');
    initializeLiveSearch('live-search-input-404', 'live-search-results-404');

    function initializeLiveSearch(inputId, resultsId) {
        const searchInput = document.getElementById(inputId);
        const searchResults = document.getElementById(resultsId);
        let selectedIndex = -1;

        if (!searchInput || !searchResults) {
            return;
        }

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.trim();
            selectedIndex = -1; // Reset selection on new input

            if (searchTerm.length > 2) {
                fetch(BASE_URL + '/utils/live-search.php?term=' + encodeURIComponent(searchTerm))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            const ul = document.createElement('ul');
                            data.forEach(item => {
                                const li = document.createElement('li');
                                const a = document.createElement('a');
                                a.href = item.url;
                                a.textContent = item.title;
                                li.appendChild(a);
                                ul.appendChild(li);
                            });
                            searchResults.appendChild(ul);
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                        searchResults.style.display = 'none';
                    });
            } else {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            const items = searchResults.querySelectorAll('li');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= items.length) {
                    selectedIndex = 0;
                }
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) {
                    selectedIndex = items.length - 1;
                }
                updateSelection(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex > -1) {
                    const selectedLink = items[selectedIndex].querySelector('a');
                    if (selectedLink) {
                        window.location.href = selectedLink.href;
                    }
                }
            }
        });

        function updateSelection(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (searchResults && !searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.style.display = 'none';
                selectedIndex = -1;
            }
        });
    }
});
