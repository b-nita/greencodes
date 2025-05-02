
document.querySelector('.search-icon').addEventListener('click', function() 
{
    const searchInput = document.getElementById('searchQuery');
    searchInput.style.display = 'block'; // Show the input field
    searchInput.focus(); // Focus on the input field
});
