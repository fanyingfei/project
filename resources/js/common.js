var max_name_len = 30;
var min_name_len = 3;
function ajax_res(obj){
    $.ajax({
        type:'POST',
        data:obj.data,
        url:obj.requestUrl,
        dataType:'json',
        success:function(result){
            if(result.status == 'error'){
                alert(result.msg);
                return false;
            }
            obj.success(result);
        },
        error:function (){
            alert('提交失败,请刷新重试');
        }
    })
}

function click_good(url , thi){
    var id = $(thi).attr('data-id');
    var type = $("#type").val();
    $.ajax({
        type:'POST',
        data:{
            "id":id,"click":'good','type':type
        },
        url:url,
        dataType:'json',
        success:function(result){
            if(result.status == 'error'){
                alert(result.msg);
                return false;
            }
            var val = $(thi).parent().children('.good').text();
            $(thi).parent().children('.good').text(parseInt(val) + 1);
            $(thi).parent().children('.response').text(result.msg);
        },
        error:function (){}
    })
}

function click_bad(url , thi){
    var id = $(thi).attr('data-id');
    var type = $("#type").val();
    $.ajax({
        type:'POST',
        data:{
            "id":id,"click":'bad','type':type
        },
        url:url,
        dataType:'json',
        success:function(result){
            if(result.status == 'error'){
                alert(result.msg);
                return false;
            }
            var val = $(thi).parent().children('.bad').text();
            $(thi).parent().children('.bad').text(parseInt(val) + 1);
            $(thi).parent().children('.response').text(result.msg);
        },
        error:function (){}
    })
}

function valid(name,email,content){
    if(name == ''){
        alert('请填写昵称');
        return false;
    }
    if(name.length > max_name_len || name.length < min_name_len){
        alert('昵称长度3-30个字符');
        return false;
    }
    if(email == ''){
        alert('请填写email');
        return false;
    }
    if(!email.match(/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/)) {
        alert('email格式不正确');
        return false;
    }
    if(content == '' || content == '<br>'){
        alert('请填写内容');
        return false;
    }
    return true;
}
$(document).ready(function(){
    var comment_url = '/record';

    $(".oo").click(function(){
        click_good(comment_url,this);
    })

    $(".xx").click(function(){
        click_bad(comment_url,this);
    })

    $("#top").click(function(){
        $('html,body').animate({scrollTop: 0}, 500);
    })

    $(".mao").click(function(){
        var height = $(document).height();
        $('html,body').animate({scrollTop: height}, 500);
    })

    $("#checkpic").click(function(){
        $(this).attr('src','/code.php?'+Math.random());
    })

    $(window).scroll(function(){
        if($(window).scrollTop() >$(window).height()/2){
            $("#top").show();
        }else{
            $("#top").hide();
        }
    });

    $(".nav li a").click(function(){
        $.cookie('search', '' ,{ path : '/' });
    })

    $(".search button").click(function(){
        var search = $("#search").val();
        $.cookie('search', search , {path:'/'});
        window.location.href=window.location.href;
    })

    $("a.reply").click(function(){
        var obj = $(this);
        var id = obj.attr('data-id');
        $.ajax({
            type:'POST',
            data:{
                "id":id
            },
            url:'/reply',
            dataType:'json',
            success:function(result){
                if(result.status == 'error'){
                    alert(result.msg);
                    return false;
                }
                if(obj.parents('.one').next().hasClass('reply_main')){
                    obj.text('↓回复');
                    obj.parents('.one').next('.reply_main').remove();
                }else{
                    obj.text('↑回复');
                    var html = '<div class="reply_main">';

                    html +='<textarea placeholder="说点什么吧"></textarea>'
                    html += '</div>';
                    obj.parents('.one').after(html);
                }
            },
            error:function (){
                alert('提交失败,请刷新重试');
            }
        })
    })
})