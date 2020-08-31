<body>
<?php
$this->session = \Config\Services::session();
if (isset($this->session->get('social')['email'])) {
    $email = $this->session->get('social')['email'];
    $pw = $this->session->get('social')['pk'];
    $type = $this->session->get('social')['type'];
}
?>
    <div id="s_register">
        <div id="s_essentialContainer">
            <div class="registerTitle">
                <span>계정, 비밀번호</span>
            </div>
            <div class="p_info_text">
                <p>
                    앤톡 계정은<br>
                    아이디로 사용합니다
                </p>
            </div>
            <div class="genderTitle nonDisplay">
                    <span>내 성별</span>
                </div>
                <div class="p_info_text nonDisplay">
                    <p>
                        성별은 필수 요소입니다<br>
                        나의 성별을 선택해주세요.
                    </p>
                </div>
            <form action="/api/oauth/create" method="post" calss="member_join" name="join" enctype="multipart/form-data" id="member_register">
                <div class="s_idContainer">
                    <label class="id" for="id">
                        <input type="text" placeholder="사용하실 아이디 입력" id="id" name="id" autocomplete="off" value="<?php echo (isset($email))?$email : "";?>" <?php echo (isset($email))? "readonly" : "";?>>
                        <input type="hidden" name="division" value="id">
                        <input type="hidden" name="type" value="email">
                        <input type="button" value="중복확인" <?php echo (isset($email))? "style='display:none'" : "";?>>
                    </label>
                </div>
                <div class="s_pwContainer" <?php echo (isset($pw))? "style='display:none'" : "";?>>
                    <label class="s_pw" for="password">
                        <input type="password" placeholder="사용하실 암호 입력" id="password" name="password" autocomplete="new-password" minlength="8" maxlength="16" value="<?php echo (isset($pw))? $pw : "";?>" <?php echo (isset($pw))? "style='display:none'" : "";?>>
                    </label>
                    <label class="s_pw_confirm" for="pw_confirm_reslut">
                        <input type="password" placeholder="사용하실 암호 입력" id="pw_confirm_result" name="pw_confirm_reslut" autocomplete="new-password" minlength="8" maxlength="16" value="<?php echo (isset($pw))? $pw : "";?>" <?php echo (isset($pw))? "style='display:none'" : "";?>>
                        <div id="pw_result"><span></span></div>
                    </label>
                </div>
                <div id="nextBtn">
                    <div class="next">다음</div>
                </div>
                
                <div class="s_genderContainer nonDisplay">
                    <div class="maleGender">
                        <div class="maleWrap">
                            <input type="radio" name="gender" id="male" value="male">
                            <div>
                                <label for="male">
                                    <img src="//ntalk.me/img/s_man_g.png">
                                </label>
                            </div>
                            <span>남성</span>
                        </div>
                    </div>
                    <div class="femaleGender">
                        <div class="femaleWrap">
                            <input type="radio" name="gender" id="female" value="female">
                            <div>
                                <label for="female">
                                    <img src="//ntalk.me/img/s_girl_g.png">
                                </label>
                            </div>
                            <span>여성</span>
                        </div>
                    </div>
                    <div id="subBtn">
                        <button class="next">다음</button>
                    </div>
                </div>
                <input type="hidden" id="snsType" name="snsType" value="<?php echo (isset($type))? $type : "";?>" >
                <input type="hidden" id="phone" name="phone">
            </form>
        </div>
    </div>
