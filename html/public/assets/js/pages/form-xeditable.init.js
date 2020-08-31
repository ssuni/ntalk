$(function(){
    $.fn.editable.defaults.mode = 'inline';
    // $.fn.editable.defaults.url = '/admin/userEdit';
    $.fn.editable.defaults.inputclass = "form-control-sm";
    $.fn.editable.defaults.params = function(params){
        params.pk = $(this).attr('data-pk');
        return params;
    }
    $.fn.editableform.buttons='<button type="submit" class="btn btn-primary editable-submit btn-sm waves-effect waves-light"><i class="mdi mdi-check"></i></button><button type="button" class="btn btn-secondary editable-cancel btn-sm waves-effect"><i class="mdi mdi-close"></i></button>',

        // ==============================================================
        // 회원
        // ==============================================================
        $("#inline-nickname").editable({
            type:"text",
            success : function(data){
                console.log(data)
            },
            error : function(xhr){
                if(xhr.status == 500) return "서버 오류"
            }
        }),
        $("#inline-phone").editable({type:"text",name:"phone",title:"Enter phone",mode:"inline",inputclass:"form-control-sm"}),
        $("#inline-sex").editable({
            prepend:"not selected",
            mode:"inline",
            source:[
                {value:"male",text:"남자"},
                {value:"female",text:"여자"}
                ],
            inputclass:"form-control-sm",
            display:function(t,e){
                var n=$.grep(e,function(e){return e.value==t});
                n.length?$(this).text(n[0].text).css("color",{"":"gray","male":"green","female":"pink"}[t]):$(this).empty()
            },
            success : function(data){
                console.log(data)
            },
            error : function(xhr){
                if(xhr.status == 500) return "서버 오류"
            }
        }),
        $("#inline-age").editable({type:"text",name:"age",title:"Enter age",mode:"inline",inputclass:"form-control-sm"}),

        $("#inline-location1").editable({
            url: '/admin/getLocation',
            prepend:"not selected",
            source: [
                {value: '서울', text: '서울'},
                {value: '경기', text: '경기'},
                {value: '강원', text: '강원'},
                {value: '부산', text: '부산'},
                {value: '인천', text: '인천'},
                {value: '대구', text: '대구'},
                {value: '광주', text: '광주'},
                {value: '대전', text: '대전'},
                {value: '울산', text: '울산'},
                {value: '세종', text: '세종'},
                {value: '충북', text: '충북'},
                {value: '충남', text: '충남'},
                {value: '경북', text: '경북'},
                {value: '경남', text: '경남'},
                {value: '전북', text: '전북'},
                {value: '전남', text: '전남'},
                {value: '제주', text: '제주'}
            ],
            success:function(data){
                console.log(data)
                $('#inline-location2').editable("destroy");
                $("#inline-location2").editable({
                    source: data
                })
                $('#inline-location2').html("");
                var $next = $(this).closest('tr').next().find('.editable');
                setTimeout(function() {
                    $next.editable('show');
                }, 300);
            }
        }),

        // ==============================================================
        // 타임라인
        // ==============================================================
        $("#timeline-phone").editable({type:"text",name:"phone",title:"Enter phone",mode:"inline",inputclass:"form-control-sm"}),
        $("#timeline-sex").editable({
            url:"/admin/timelineEdit",
            prepend:"not selected",
            source:[
                {value:"all",text:"전체"},
                {value:"male",text:"남자"},
                {value:"female",text:"여자"}
            ],
            inputclass:"form-control-sm",
            display:function(t,e){
                var n=$.grep(e,function(e){return e.value==t});
                n.length?$(this).text(n[0].text).css("color",{"":"gray","male":"green","female":"pink"}[t]):$(this).empty()
            },
            success : function(data){
                console.log(data)
            },
            error : function(xhr){
                if(xhr.status == 500) return "서버 오류"
            }
        })

        $("#timeline-title").editable({type:'text',name:'Enter title',})
        $("#timeline-comment").editable({type:'text',name:'Enter title',})
        $("#timeline-img").editable({type:'text',name:'Enter title',})


});