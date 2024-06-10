
function confirmDeletion(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        window.location.href = '/public/admin/posts/delete_post.php?id=' + postId;
    }
}