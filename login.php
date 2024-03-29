<?php error_reporting(E_ERROR | E_PARSE); ?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LDAP login</title>
    <!-- PUBLIC KEY, FOR TESTING PURPOSES -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></script>
    <script>
    grecaptcha.ready(function() {
        //PRIVATE KEY, FOR TESTING PURPOSES
    grecaptcha.execute('6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', {action: 'comentario'})
    .then(function(token) {
    var recaptchaResponse = document.getElementById('recaptchaResponse');
    recaptchaResponse.value = token;
    });
    });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css"  integrity="undefined" crossorigin="anonymous">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <?php
if (!empty($login_err))
{
    echo '<div class="alert alert-danger">' . $login_err . '</div>';
}
?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
        </p>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            </form>
    </div>
</body>
</html>

<?php
// Initialize the session
session_start();

//Captcha
require_once "includes/recaptchalib.php";

//LDAP CONFIGURATION
$ldapconfig['host'] = ''; //CHANGE THIS
$ldapconfig['port'] = '389'; //CHANGE THIS
$ldapconfig['basedn'] = ''; //CHANGE THIS
$ldapconfig['usersdn'] = ''; //CHANGE THIS
$conn = ldap_connect($ldapconfig['host'], $ldapconfig['port']);

ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

// Define variables
if (isset($_POST["username"]) && isset($_POST["password"]))
{
    $username_ldap = $_POST['username'];
    $password_ldap = $_POST['password'];
    $dn = "uid=" . $username_ldap . "," . $ldapconfig['usersdn'] . "," . $ldapconfig['basedn'];
}

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
{
    header("location: welcome.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST")
{

    //Testing purpose CAPTCHA api keys, **CHANGE
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
    $recaptcha_response = (isset($_POST['recaptcha_response']));

    // Make and decode POST request:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);

    // Take action based on the score returned:
    //if ($recaptcha->score >= 1) { //DISCOMMENT WHEN PUTTING IN PRODUCTION
    

    // Check if username adn password input is empty
    if (empty(trim($_POST["username"])))
    {
        $username_err = "Enter username.";
    }
    else
    {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"])))
    {
        $password_err = "Enter your password.";
    }
    else
    {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err))
    {

        if ($bind = ldap_bind($conn, $dn, $password))
        {
            echo ("Login correct");

            session_start();

            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["username"] = $username;

            // Filter user by group
            
            $attributes = array(
                "givenname"
            );
            $filter = "memberUid=$username"; //CHANGE
            $result = ldap_search($conn, 'ou=,dc=,dc=,dc=', $filter, $attributes); //CHANGE
            $entries = ldap_get_entries($conn, $result);
            if ($entries["count"] > 0)
            {
                header("location: welcome.php"); //CHANGE
            }
            elseif ($entries["count"] == 0)
            {
                $attributes_a = array(
                    "givenname"
                );
                $filter_a = "memberUid=$username"; //CHANGE
                $result_a = ldap_search($conn, 'cn=administradores,ou=grupos,dc=jon,dc=v3l4r10,dc=eus', $filter_a, $attributes_a); //CHANGE
                $entries_admin = ldap_get_entries($conn, $result_a);
                if ($entries_admin["count"] > 0)
                {
                    header("location: welcome_admin.php"); //CHANGE

                }
            }
            else
            {
                exit();
            }

        }
        else
        {
            echo ("Connection error");
        }

    }
    else
    {
        echo ("Incorrect credentials");
    }
}

?>
