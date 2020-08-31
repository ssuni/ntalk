<script>
   function login_checked () {
        var input_id = $('#user').val(),
            input_pw = $('#psw').val();
        console.log(input_id);
        console.log(input_pw);
        if (input_id != '' && input_pw == '') {
            alert('비밀번호를 입력해 주세요');
            return false;
        } else {
            return true;
        }
    }
</script>
<div id="warp">
    <div id="loginSection">
        <header>
            <div id="loginTitle">
                <span>로그인</span>
            </div>
        </header>
        <form name="userInfo" method="post" action="/auth/do_login" onsubmit="return login_checked();">
            <input type="hidden" name="division" value="pc" />
            <div id="memberInfo">
                <label class="id_text" for="user">
                    <input type="text" maxlength="30" id="user" name="user" placeholder="아이디를 입력해주세요" value="<?php if (isset($user)) : ?><?php echo $user; ?><?php endif ?>" autocomplete="off" autofocus>
                </label>
            </div>
            <div id="memberPw">
                <label class="pw_text" for="psw">
                    <input type="password" maxlength="16" id="psw" name="password" placeholder="비밀번호를 입력해주세요" value="<?php if (isset($password)):?><?php echo $password;?><?php endif ?>" autocomplete="new-password">
                </label>
            </div>
            <div id="searchContainer">
                <!-- <div class="idMemory">
                    <input type="radio" id="memory">아이디 저장
                </div> -->
                <div class="toggleWrap">

                    <input type="checkbox" id="idsave" value="" />

                    <div>
                        <label for="idsave">
                            <span />
                        </label>
                    </div>
                    <span>아이디 저장</span>
                </div>
                <div class="idSearch">
                    <span>아이디 찾기</span> | 
                    <span>비밀번호 초기화</span>
                </div>
            </div>
            <div class="memberInfoSubmit">
                <label class="info_reslut" for="login_submit">
                    <input type="submit" id="login_submit" name="login_submit" value="로그인">
                </label>
            </div>
        </form>
        <footer id="easyLogin">
            <div class="loginContainer">
                <div class="easyGoogle">
                    <a href="https://ntalk.me/api/oauth_social/googleLogin">
                        <em class="googleLogo"></em>
                        <span>
                            구글 계정으로 로그인
                        </span>
                    </a>
                </div>
                <div class="easyNaver">
                    <a href="https://ntalk.me/api/oauth_social/naverLogin">
                        <em class="naverLogo"></em>
                        <span>
                            네이버 계정으로 로그인
                        </span>
                    </a>
                </div>

                <div class="easyKakao">
                    <a href="https://ntalk.me/api/oauth_social/kakaoLogin">
                        <em class="kakaoLogo"></em>
                        <span>
                            카카오 계정으로 로그인
                        </span>
                    </a>
                </div>
                <div class="easyFacebook">
                    <a href="https://ntalk.me/api/oauth_social/facebookLogin">
                        <em class="facebookLogo"></em>
                        <span>
                            페이스북 계정으로 로그인
                        </span>
                    </a>
                </div>
            </div>
        </footer>
    </div>
    <!-- <div id="logininfo">
        <div class="infoTItle">
            <span>하루 100만명의 회원님들과<br>매일 만나는 앤톡,<br>즐겁고 특별한 인연을 만들어보세요!</span>
        </div>
        <div class="infoContent">
            <span>회원가입하시고 네이버 TV에서 월드방송아이비브이채널에<br>
                구독 눌러 주신 분들께 전국 어디에서나를 보내 드립니다<br>
                자세한 내용은 월드방송 홈피 팝업창을 참고해 주시고<br>
                본인이 부담하여야 하며 원하지 않을 경우 않으셔도 됩니다.</span>
        </div>
        <div id="registerSection">
            <div id="membershipJoin">
                <a href="/auth/terms">앤톡 회원 가입하기<span>></span></a>
            </div>
        </div>
    </div> -->
</div>