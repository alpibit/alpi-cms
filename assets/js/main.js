
function confirmDeletion(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        window.location.href = 'posts/delete.php?id=' + postId;
    }
}