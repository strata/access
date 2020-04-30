<?php

get_header();
?>

<style type="text/css">
form#access_otp {
    width: 50%;
    margin: 20px auto;
    border: #373151 5px solid;
    background: #edebea;
    color: #000;
    font-family: sans-serif;
    box-shadow: 5px 5px 3px 0px rgba(100, 100, 100.87);
    line-height:160%;
    padding:10px; 0;
}
#access_otp input[type="text"] {
    display:block;
    width:90%;
    font-size:21px;
    padding:7px;
}
#access_otp h4 {
    font-size:21px;
}
#access_otp p,h4 {
    padding:0 20px;
}
</style>

<form id="access_otp" action="/en/access/verify" method="post">

    <h4>Access Verification Required</h4>

    <p><label for="access_otp_email">Please enter your email before proceeding.</label>
        <input type="text" name="access_otp_email" id="access_otp_email" placeholder="email@domain.tld">
    </p>
    <p><button type="submit">Send One-Time-Password</button></p>

</form>


<?php get_footer(); ?>
