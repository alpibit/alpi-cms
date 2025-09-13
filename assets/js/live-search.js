document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('live-search-input');
    const searchResults = document.getElementById('live-search-results');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.trim();

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

        document.addEventListener('click', function (e) {
            if (searchResults && !searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.style.display = 'none';
            }
        });
    }
});
