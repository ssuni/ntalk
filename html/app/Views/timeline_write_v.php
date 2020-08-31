<!-- <div id="cside">
    <div class="wrapper">
        <div id="contentFrame" class="contentFrame"> -->
        <section id="writepopup">
            <div id="timeWrite">
                <div id="titleContainer">
                    <div class="writeTitle">타임라인 글쓰기</div>
                    <div class="closeBtn">
                            <img src="https://image.flaticon.com/icons/svg/61/61155.svg">
                    </div>
                </div>
                <div id="contentContainer">
                    <form id="timeLineAdd" method="post" action="/api/Timeline/timeline_insert" enctype="multipart/form-data"> 
                        <div id="contentTitle">
                            <div class="titleText">제목</div>
                            <input type="text" id="conTitle" name="title" placeholder="제목">
                        </div>
                        <div class="genderTag">
                            <div class="genderTitle">성별</div>
                            <div class="all" value="all">모두</div>
                            <div class="female" value="female">여자</div>
                            <div class="male" value="male">남자</div>
                        </div>
                        <div class="ageTag">
                            <div class="beforeAge">
                                <select name="minAge">
                                    <option value="">연령</option>
                                </select>
                            </div> ~ 
                            <div class="afterAge">
                                <select name="maxAge">
                                    <option value="">연령</option>
                                </select>
                            </div>
                        </div>
                        <div class="locationTag">
                            <div class="locationTitle">지역선택</div>
                        </div>
                        <div class="imgTag">
                            <input type="file" id="file_img" name="img[]">
                        </div>
                        <div class="contentTag">
                            <textarea type="text" id="time_input" cols="41" rows="5" name="comment" class="msg" style="resize: none;" autofocus autocomplete="off" wrap="hard" placeholder="내용"></textarea>
                        </div>
                        <div id="submitContainer">
                            <input type="submit" id="sub" value="타임라인 등록">
                        </div>
                    </form>    
                </div>
            </div>
        </selction>
      <!--   </div>
    </div>
</div> -->