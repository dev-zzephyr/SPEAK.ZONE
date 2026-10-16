// alert(auth);

function goWrite() {
    if (auth == 0) alert("로그인이 필요합니다.");
    else location.href = "write.php";
}

function viewNotification() {
    if (auth == 0) alert("로그인이 필요합니다.");
}

function viewMyAccount() {
    if (auth == 0) alert("로그인이 필요합니다.");
}