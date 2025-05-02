 <?php
// session_start();
// $connection = mysqli_connect("localhost","root","","database");
// if(isset($_POST["save_data"]))
// {
//     $name = $_POST["name"];
// $email = $_POST["email"];

// $subject = $_POST["email"];
// $message = $_POST["message"];

// $insert_query = "INSERT INTO contact_submissions(name, email, subject, message) VALUES('$name','$email','$subject','$message')";
// $insert_query_run = mysqli_query($connection, $insert_query);



// if ($insert_query_run)

// {
//     $_SESSION['status'] = 'success';
//     header('location: index.php');

// }
// else{
//     $_SESSION['status'] = 'failed';
//     header('location: index.php');

// }
// }
?> 
<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "database");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update contact
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    mysqli_query($conn, "UPDATE contact_submissions SET name='$name', email='$email', subject='$subject', message='$message' WHERE id=$id");
    exit; // return nothing (AJAX expects no HTML here)
}

// For AJAX "get" contact data
if (isset($_POST['get_id'])) {
    $id = $_POST['get_id'];
    $result = mysqli_query($conn, "SELECT * FROM contact_submissions WHERE id=$id");
    echo json_encode(mysqli_fetch_assoc($result));
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>Edit Modal Example</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container py-4">

<h2>Contact List</h2>
<table class="table">
  <thead>
    <tr><th>Name</th><th>Email</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM contact_submissions");
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>
              <td>{$row['name']}</td>
              <td>{$row['email']}</td>
              <td><button class='btn btn-sm btn-primary editBtn' data-id='{$row['id']}'>Edit</button></td>
            </tr>";
    }
    ?>
  </tbody>
</table>

<!-- Bootstrap Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="editForm">
      <div class="modal-header">
        <h5 class="modal-title">Edit Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="id">
        <div class="mb-3">
          <label>Name*</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email*</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Subject</label>
          <input type="text" name="subject" id="subject" class="form-control">
        </div>
        <div class="mb-3">
          <label>Message*</label>
          <textarea name="message" id="message" class="form-control" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
// Open modal and fetch data
$(document).on('click', '.editBtn', function () {
  var id = $(this).data('id');
  $.post('', { get_id: id }, function (data) {
    $('#id').val(data.id);
    $('#name').val(data.name);
    $('#email').val(data.email);
    $('#subject').val(data.subject);
    $('#message').val(data.message);
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
  }, 'json');
});

// Submit form via AJAX
$('#editForm').submit(function (e) {
  e.preventDefault();
  $.post('', $(this).serialize(), function () {
    alert('Updated!');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    location.reload();
  });
});
</script>

</body>
</html>
