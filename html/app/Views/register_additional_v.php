<script>
    function skip_btn_result () {
        var select_text = $('#submitBtn  div:nth-child(1)').attr('class'),
            img_adr = $('#profile');

            console.log($(this));
            console.log($(this).children().text());
            console.log(select_text);
        if(select_text == 'skipBtn backColor'){
            img_adr.val('');
            return true;
        }else {
            return true;
        }
    }
</script>
<div id="addRegister">
    <div id="addProfileSection">
        <div id="firstCreate" class="firstCreate">
            <div class="crearteImg">
                <img src="//ntalk.me/img/join_girl.gif">
            </div>
        
            <div class="createTitle">
                <span>프로필을 만들어보세요</span>
            </div>
            <div class="createContent">
                <span>내 프로필을 만드시면<br>
                    회원님들에게 나를 어필하실 수 있습니다.
                </span>
            </div>
            <div class="startNtalk" id="startNtalk"><a href="//ntalk.me/">앤톡 시작하기</a></div>
            <div class="addProfile" id="addProfile">프로필 만들기</div>
        </div>
    </div>
    <div id="addInputSection" class="addProfileSection nonDisplay">
        <div id="addInput" class="addInput">
            <form action="/api/oauth/createAdditional" method="post" calss="member_join" name="join" enctype="multipart/form-data" id="register_add" onsubmit="return skip_btn_result();">
                <div id="s_Additional_information">
                    <div class="addInfoTitle">
                        <span>추가정보</span>
                    </div>
                    <div class="addInfoContent">
                        <span>
                            닉네임, 나이, 지역을<br>
                            설정해주세요.
                        </span>
                    </div>
                    <div class="s_nickContainer">
                        <label class="s_nick" for="nickname">
                            <input type="text" placeholder="닉네임" id="nickname" name="nickname" autocomplete="off">
                            <input type="hidden" name="division" value="nickname">
                            <input type="button" value="중복확인">
                        </label>
                    </div>
                    <!-- <div class="s_fav_genderContainer">
                        <label>선호 성별
                            <input type="radio" name="favorite_gender" value="male">남자
                            <input type="radio" name="favorite_gender" value="female">여자
                            <input type="radio" name="favorite_gender" value="all">모두
                        </label>
                    </div> -->
                    <div class="s_ageContainer">
                        <label>
                            <input type="text" name="age" id="age" autocomplete="off" placeholder="나이 (19세 ~ 60세까지)" maxlength="2" pattern="^([0-9][0-9]?|)$">
                        </label>
                    </div>
                    <div id="selectBtn">
                        <div class="completeBtn">
                            <span>건너뛰기</span>
                        </div>
                        <div class="nextBtn">
                            <span>다음</span>
                        </div>
                    </div>
                </div>
                <div id="s_joinContainer" class="s_joinContainer nonDisplay">
                    <div class="ImgTitle">
                        <span>프로필 사진</span>
                    </div>
                    <div class="imgContent">
                        <span>
                            나를 어필할수 있는<br>
                            사진을 등록해주세요.
                        </span>
                    </div>
                    <div class="imgUpdate">
                        <img src="//ntalk.me/img/people_base_back.svg">
                        <label for="profile">
                            <input type="file" name="profile" id="profile">
                            <div id="preview"></div>
                        </label>
                        <div class="secondBtn nonDisplay"><span></span></div>
                    </div>
                    <div id="submitBtn">
                        <div class="skipBtn">
                            <button>건너뛰기</button>
                        </div>
                        <div class="joinBtn">
                            <button>회원가입</button>
                        </div>
                    </div>
                    <input type="hidden" name="id" id="cookieId">
                </div>
            </form>
        </div>
    </div>
</div>