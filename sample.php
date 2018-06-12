<html>

<body>
    <form method="post">
        <p>
            API key:<br/>
            <input type="text" name="apikey" value=""/>
        </p>
        <p>
            From name:<br/>
            <input type="text" name="fromName" value="John Doe"/>
        </p>
        <p>
            From Email:<br/>
            <input type="email" name="fromEmail" value="smtpapi@donatus.hu"/>
        </p>
        <p>
            To name:<br/>
            <input type="text" name="toName" value=""/>
        </p>
        <p>
            To Email:<br/>
            <input type="email" name="toEmail" value=""/>
        </p>
        <p>
            Subject:<br/>
            <input type="text" name="subject" value="Test <?= date('Y.m.d. H:i:s'); ?>"/>
        </p>
        <p>
            Body:<br/>
            <textarea name="body">Asd</textarea>
        </p>
        <button type="submit">Send</button>
    </form>

    <?php

    require 'classes/common.php';

    $apiurl = 'http://localhost/my-smtp-api.git/smtp';
    $apikey = isset($_POST['apikey']) ? $_POST['apikey'] : '';
    $fromName = isset($_POST['fromName']) ? $_POST['fromName'] : '';
    $fromEmail = isset($_POST['fromEmail']) ? $_POST['fromEmail'] : '';
    $toName = isset($_POST['toName']) ? $_POST['toName'] : '';
    $toEmail = isset($_POST['toEmail']) ? $_POST['toEmail'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $body = isset($_POST['body']) ? $_POST['body'] : '';

    $request = json_encode([
        'from' => [
            'name' => $fromName,
            'email' => $fromEmail
        ],
        'to' => [
            'name' => $toName,
            'email' => $toEmail
        ],
        'subject' => $subject,
        'body' => $body,
        'isHTML' => true,
        'charset' => 'UTF-8',
    ]);

    ?>

    <h1>Request JSON</h1>
    <code>
        <?= $request; ?>
    </code>

    <?php

    $mail = Common::CallAPI('POST', $apiurl, [
        "apikey" => $apikey,
        "mail" => $request
        ]);
    ?>

    <h1>Response JSON</h1>
    <code>
        <?php echo $mail; ?>
    </code>

</body>
 
</html>