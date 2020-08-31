<div id="myPage">
    <div id="myPageheader">
        <div id="myPageTitle">
            마이페이지
        </div>
    </div>
    <div id="myPageContent">
        <div class="accountContainer">
            <div class="accountTitle">
                <span>계정정보</span>
            </div>
        </div>
        <div class="accountContent">
            <div class="loginAccountContent">
                <span>로그인된 계정</span>
            </div>
            <div class="loginAccount">
                <span class="accountImg"></span><span></span>
            </div>
            <div class="accountChange">
                <span><a href="https://ntalk.me/api/oauth/logout" class="">로그아웃</a></span>
            </div>
        </div>
        <div class="difAccountContent">
            <div class="difAccountTitle">
                <span>내 다른 계정</span>
            </div>
            <div class="difAccountId"></div>
        </div>
        <div class="pswContent">
            <div class="pswTitle">
                <span>비밀번호</span>
            </div>
            <div class="psw">
                <span class="pswNon">**********</span>
                <span class="pswChangeDate"></span>
            </div>
            <div class="pswChange">
                <span>재설정</span>
            </div>
        </div>
        <div class="phoneContent">
            <div class="phoneTitle">
                <span>휴대폰</span>
            </div>
            <div class="phone">
                <span></span>
            </div>
            <div class="phoneChange">
                <span>번호변경</span>
            </div>
        </div>
        <div class="contentContainer">
            <div class="myInfoTitle">
                <div>내정보</div>
            </div>
        </div>
        <div class="profileContent">
            <div class="profileTitle">
                <span>프로필사진</span>
            </div>
            <div id="profileImg">
                <img id="preview" src="">
                <label for="profile_change">
                    <span class="editBtn"></span>
                </label>
                <!--img id="clickImg" src=""-->
            </div>
            <label for="profile_change">
                <div id="profile_change">프로필 변경</div>
                    <!-- <input type="file" name="profile" id="profile" class="newProfile"> -->
            </label>
        </div>
        <div class="nickContent">
            <div class="nickTitle">
                <span>닉네임</span>
            </div>
            <div class="myNickname">
                <span></span>
            </div>
        </div>
        <div class="genderContent">
            <div class="genderTitle">
                <span>성별</span>
            </div>
            <div class="gender">
                <span></span>
            </div>
        </div>
        <div class="ageContent">
            <div class="ageTitle">
                <span>나이</span>
            </div>
            <div class="age">
                <span></span>
            </div>
        </div>
        <div class="locationContent">
            <div class="locationTitle">
                <span>지역</span>
            </div>
            <div class="location">
                <span class="mylocation"></span>
                <span class="mylocation2"></span>
            </div>
        </div>
        <div id="mytimeline">
            <div class="myTime">
                <div class="myTimeTitle">내 타임라인</div>
                <div class="myTimeContent">
                    <div class="myProfileImg">
                        <!-- <img src=""> -->
                    </div>
                    <div class="commentContainer">
                        <div class="mycommentTitle"></div>
                        <div class="myComment"><span></span></div>
                    </div>
                    <div class="myTimeDelBtn">삭제</div>
                    <div class="myTimeEditBtn">수정</div>
                </div>
                <input type="hidden" name="content_id">
            </div>
            <div class="nonTime nonDisplay">
                <p>등록된 타임라인이 존재 하지 않습니다.<br>
                    타이라인을 작성해 보세요.</p>
            </div>
        </div>
        <div class="photoContainer">
            <div class="photoTitle">
                <span>내 사진</span>
            </div>
            <div id="photoContent">
                <!-- <img src="" id="photoAdd"> -->
            </div>
        </div>
        <div class="cutoffContainer">
            <div class="cutoffTitle">
                <span>차단설정</span>
                <span class="cutoffBtn">차단해제</span>
            </div>
            <div class="cutoffContent">
                <div class="cutoff">
                    <label class="cutoffcheck" for="cutoffcheck">
                        <input type="checkbox" id="cutoffcheck">
                        <span></span>
                    </label>
                    <div class="cutoffUser">
                        <img src="https://img.hankyung.com/photo/201808/01.17511254.1.png" id="cutoffImg">
                        <div class="cuoffUserInfo">
                            <div class="cutoffNick">알로항항</div>
                            <div class="cutoffDate">2019.05.24</div>
                        </div>
                    </div>
                </div>
                <div class="cutoff">
                    <label class="cutoffcheck" for="cutoffcheck">
                        <input type="checkbox" id="cutoffcheck">
                        <span></span>
                    </label>
                    <div class="cutoffUser">
                        <img src="https://pds.joins.com/news/component/htmlphoto_mmdata/201911/15/e078b766-9c10-4244-a166-e4a19c8596e1.jpg.tn_350.jpg" id="cutoffImg">
                        <div class="cuoffUserInfo">
                            <div class="cutoffNick">향숙이</div>
                            <div class="cutoffDate">2019.05.30</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="detail_see">더보기<span></span></div>
        </div>
    </div>
</div>