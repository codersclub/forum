<?php
// Sources.ru Donation Script
require __DIR__ . '/../app/bootstrap.php';

$tMessage = '';
$right_referer = 'https://www.sources.ru/donate.php';
$right_referer = 'https://sources/donate.php';

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$referer = mb_strtolower($referer);

/*
  // Check for proper referer

  if($_SERVER['REQUEST_METHOD'] == 'POST')
  {
    if(!preg_match("#sources(\.ru)?/donate\.php#i",$referer)) {
      $error = 'referer';
      header('Location: '.$right_referer);
      exit;
    }
  }
*/


@header("Content-type: text/html; charset=utf-8");


//-----------------------------------------------------------------
// encode_header
// Action: Encode a string according to RFC 1522 for use in headers
// if it contains 8-bit characters.
// Call: encode_header (string header, string charset)
//
function encode_header($string, $charset = 'iso-8859-1')
{
    // Check for non-ASCII chars in the string:

    if (preg_match("#[\x80-\xFF]#", $string)) {
        // The string contain non-ASCII characters, encode this!
        return '=?' . $charset . '?B?' . base64_encode($string) . '?=';
    }

    // The string is ASCII-compatible, return as is:
    return $string;
}


?>
<!doctype html>
<html lang="ru">
<head>
    <title>Помощь проекту Исходники.RU</title>
    <?=Assets::make('assets/stylesheets/donate.scss')?>
    <script><!--
        function gourl(s) {
            window.top.location.href = s;
        }
        function check() {
            if (document.myform.name.value == '') {
                alert('Нужно заполнить поле\n"Ник (или ФИО)"');
                document.myform.name.focus();
                return false;
            }
            if (document.myform.date.value == '') {
                alert('Нужно заполнить поле\n"Дата платежа"');
                document.myform.date.focus();
                return false;
            }
            if (document.myform.currency.value == '') {
                alert('Нужно заполнить поле\n"Валюта"');
                document.myform.currency.focus();
                return false;
            }
            if (document.myform.summ.value == '') {
                alert('Нужно заполнить поле\n"Сумма"');
                document.myform.summ.focus();
                return false;
            }
            if (document.myform.summ.value <= 0) {
                alert('Нужно заполнить поле\n"Сумма"');
                document.myform.summ.focus();
                return false;
            }

            var name = document.myform.name.value.toLowerCase();
            var date = document.myform.date.value.toLowerCase();
            var currency = document.myform.currency.value.toLowerCase();
            var summ = document.myform.summ.value.toLowerCase();
            var email = document.myform.email.value.toLowerCase();
            var thestr = ' ' + name + ' ' + date + ' ' + currency + ' ' + summ + ' ' + email;
            var ind = thestr.indexOf("http:");

            if (ind >= 0) {
                alert('Sorry, NO links enabled here!');
                return false;
            }

            /*
             if(!confirm('Проверьте еще раз правильность введенной информации!\n'+
             'Если все заполнено правильно, нажмите OK.\n'+
             'Для внесения исправлений нажмите Отмена.'
             )) {
             return false;
             }
             alert('Спасибо за Ваше сообщение!\nИнформация о платеже отправлена администратору.');
             */
            return true;

        }
        function clearall() {
            document.myform.reset();
            document.myform.name.value = '';
            document.myform.date.value = '';
            document.myform.currency.value = '';
            document.myform.summ.value = '';
            document.myform.email.value = '';
            return true;
        }
        //--></script>
    <link rel="stylesheet" href="html/calendar/calendar.css">
    <script src="html/calendar/calendar.js"></script>
    <script src="html/calendar/calendar-ru.js"></script>
    <script src="html/calendar/calendar-setup.js"></script>

</head>


<body>

<table cellspacing=0 cellpadding=0>
    <tr>
        <td width=220><a href="/index.html"><img hspace=12 src="/img/jassy.gif"></a><br>&nbsp;
        </td>
        <td align="center">
            <BR>

            <H1>Помощь проекту</H1>
            <br>

            <!--TopList COUNTER-->
	    <a target=_top href="https://top.list.ru/jump?from=89876">
                <script><!--
                    d = document;
                    a = '';
                    a += ';r=' + escape(d.referrer)
                    js = 10//--></script>
                <script language="JavaScript1.1"><!--
a+=';j='+navigator.javaEnabled()
js=11//-->
                </script>
                <script language="JavaScript1.2"><!--
                    s = screen;
                    a += ';s=' + s.width + '*' + s.height
                    a += ';d=' + (s.colorDepth ? s.colorDepth : s.pixelDepth)
                    js = 12//--></script>
                <script language="JavaScript1.3"><!--
js=13//-->
                </script>
                <script language="JavaScript"><!--
                    d.write('<img src="https://top.list.ru/counter' +
                        '?id=89876;t=57;js=' + js + a + ';rand=' + Math.random() +
                        '" alt="TopList" ' + 'height=1 width=1>')
                    if (js > 11)d.write('<' + '!-- ')//--></script>
                <noscript><img
                        src="https://top.list.ru/counter?js=na;id=89876;t=57"
                        height=1 width=1
                        alt="TopList"></noscript>
                <script><!--
                    if (js > 11)d.write('--' + '>')//--></script>
            </a><!--TopList COUNTER-->

            <!--Rambler-->
	    <a href="https://counter.rambler.ru/top100/">
		<img src="https://counter.rambler.ru/top100.cnt?163871" alt="Rambler's Top100" width=1 height=1>
	    </a>

            <!-- SpyLOG f:0211 -->
            <script>
                u = "u1624.10.spylog.com";
                d = document;
                nv = navigator;
                na = nv.appName;
                p = 1;
                bv = Math.round(parseFloat(nv.appVersion) * 100);
                n = (na.substring(0, 2) == "Mi") ? 0 : 1;
                rn = Math.random();
                z = "p=" + p + "&rn=" + rn;
                y = "";
                y += "<a href='https://" + u + "/cnt?f=3&p=" + p + "&rn=" + rn + "' target=_blank>";
                y += "<img src='https://" + u + "/cnt?" + z +
                    "&r=" + escape(d.referrer) + "&pg=" + escape(window.location.href) + "' width=1 height=1 alt='SpyLOG'>";
                y += "</a>";
                d.write(y);
                if (!n) {
                    d.write("<" + "!--");
                }//--></script>
            <noscript>
                <a href="https://u1624.10.spylog.com/cnt?f=3&p=1" target=_blank>
                    <img src="https://u1624.10.spylog.com/cnt?p=1" alt='SpyLOG' width=1 height=1>
                </a></noscript>
            <script language="javascript1.2"><!--
                if (!n) {
                    d.write("--" + ">");
                }//--></script>
            <!-- SpyLOG -->

            <!-- HotLog -->
            <script>
                hotlog_js = "1.0";
                hotlog_d = document;
                hotlog_n = navigator;
                hotlog_rn = Math.random();
                hotlog_n_n = (hotlog_n.appName.substring(0, 3) == "Mic") ? 0 : 1;
                hotlog_r = "" + hotlog_rn + "&s=14399&r=" + escape(hotlog_d.referrer) + "&pg=" +
                    escape(window.location.href);
                hotlog_d.cookie = "hotlog=1";
                hotlog_r += "&c=" + (hotlog_d.cookie ? "Y" : "N");
                hotlog_d.cookie = "hotlog=1; expires=Thu, 01-Jan-70 00:00:01 GMT"</script>
            <script language="javascript1.1">
hotlog_js="1.1";hotlog_r+="&j="+(navigator.javaEnabled()?"Y":"N")
            </script>
            <script language="javascript1.2">
                hotlog_js = "1.2";
                hotlog_s = screen;
                hotlog_r += "&wh=" + hotlog_s.width + 'x' + hotlog_s.height + "&px=" + ((hotlog_n_n == 0) ?
                    hotlog_s.colorDepth : hotlog_s.pixelDepth)</script>
            <script language="javascript1.3">hotlog_js="1.3"</script>
            <script>hotlog_r += "&js=" + hotlog_js;
                hotlog_d.write("<img src='https://hit2.hotlog.ru/cgi-bin/hotlog/count?'+hotlog_r+'&' width=1 height=1>")</script>
            <noscript><img src="https://hit2.hotlog.ru/cgi-bin/hotlog/count?s=14399"
                           width=1 height=1></noscript>
            <!-- /HotLog -->


        </td>

    </tr>
</table>

<br>


<HR>



<?php
$name = 'Incognito';
$date = date("d.m.Y");
$currency = '';
$summ = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get Form Parameters
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    foreach ($_POST as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $kk => $vv) {
                $_POST[$k][$kk] = str_replace("\r", "", $vv);
                $_POST[$k][$kk] = str_replace("\n", "", $vv);
                $_POST[$k][$kk] = str_replace("<", "&lt;", $vv);
                $_POST[$k][$kk] = str_replace(">", "&gt;", $vv);
            }
        } else {
            $_POST[$k] = str_replace("\r", "", $v);
            $_POST[$k] = str_replace("\n", "", $v);
            $_POST[$k] = str_replace("<", "&lt;", $v);
            $_POST[$k] = str_replace(">", "&gt;", $v);
        }
    }
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $currency = isset($_POST['currency']) ? $_POST['currency'] : '';
    $summ = isset($_POST['summ']) ? $_POST['summ'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';


    // Validate Form Parameters
    if (!preg_match("#^(\d\d\.){2}(\d{4})$#i", $date)) {
        $tMessage = 'Error. Invalid DATE entered!';
    } else {
        if (!preg_match("#^(WMZ|WME|WMR|WMU|Yandex.Money)$#i", $currency)) {
            $tMessage = 'Error. Invalid CURENCY entered!';
        } else {
            if (!preg_match("#^\d+(\.\d+)*#i", $summ)) {
                $tMessage = 'Error. Invalid SUMM entered!';
            } else {
                if ($summ <= 0) {
                    $tMessage = 'Error. Invalid SUMM entered!';
                }
            }
        }
    }

    $to = 'rswag@sources.ru';
    $from = 'donation@sources.ru';
    $subject = 'Sources.ru Donation';
    $subject = encode_header($subject, 'utf-8');

    $headers = '';
    $headers .= "From: " . $from . "\n";
    $headers .= "Return-Path: <noreply@sources.ru>\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\n";
    $headers .= "Content-Transfer-Encoding: 8bit\n";

    $message = "Sources.ru Donation Report\n\n";

    $message .= "Remote_addr: " . $remote_addr . "\n";
    $message .= "Referer:     " . $referer . "\n";
    $message .= "Subject:     " . $subject . "\n";
    $message .= "Name:        " . $name . "\n";
    $message .= "Date:        " . $date . "\n";
    $message .= "Currency:    " . $currency . "\n";
    $message .= "Summ:        " . $summ . "\n";
    $message .= "Email:       " . $email . "\n";
//  $message .= "Error:       ".$tMessage."\n";

    // DEBUG
    $debug = '';
//  $debug = "From: " . $from . "<br>\n";
    $debug .= "To: " . $to . "<br>\n";
    $debug .= "Subject: " . $subject . "<br>\n";
    $debug .= str_replace("\n", "<br>\n", $headers);
    $debug .= "<br>\n";
    $debug .= str_replace("\n", "<br>\n", $message);
    $debug .= "<br>\n";
//  echo $debug;

    if (!$tMessage) {
        // Send Mail to the Receiver
        if (@mail($to, $subject, $message, $headers, '-f' . $from)) {
            $tMessage = "Thank you for your report!<br><br>Your report is successfully delivered to the server admin.";
        } else {

            // Report error if can't send email
            $error = 'mailerror';
            $tMessage = "Sorry, an error occured!<br><br>Could not send the email.";
        }
    }
}

?>



<a name=home></a>

<H2>
    Спасибо за Ваш интерес к нашему проекту!
</H2>

<P>
    Проект "Исходники.ру" является некоммерческим ресурсом
    и существует только благодаря энтузиазму его авторов,
    финансирующих проект с 2000 года из своего собственного кармана.
</p>

<P>
    Если Вы готовы оказать поддержку нашему проекту,
    мы с благодарностью примем любую посильную помощь!
</p>

<P>
    <b>Варианты оказания помощи проекту:</b>

<ul type="square">
    <li><a href="#finance">Финансовая помощь</a></li>
    <li><a href="#prize">Предоставление призов для проведения конкурсов и викторин</a></li>
    <li><a href="/advert/">Размещение Вашей рекламы на страницах наших сайтов</a></li>
</ul>
</P>


<HR>


<a name=finance></a>

<H2>
    Финансовая помощь проекту
</H2>

<P>
    Если у Вас есть электронные кошельки, Вы можете перечислить
    любую необременительную для Вас сумму на один из наших электронных кошельков:

<table cellpadding=8>
    <tr valign="top">
        <td>
            <a target="_blank" href="https://www.webmoney.ru"><b>WebMoney:</b></a>
            <br>
            <br>
            Z293399007548<br>
            E269636861423<br>
            R344321089231<br>
            U365486311204<br>
        </td>
        <td>
            <a target="_blank" href="https://money.yandex.ru/"><b>Яндекс.Деньги:</b></a>
            <br>
            <br>
            41001151000887
        </td>
    </tr>
</table>
</p>

<p>
    Возможные методы оплаты приведены в разделе
    <a href="#payment">Как перевести деньги</a>
</p>


<P>
    Если Вы хотите, чтобы Ваш платеж не был безымянным,
    сообщите нам о произведенном Вами платеже!
    <br>
    Для этого укажите в пояснении к платежу Ваш ник на нашем форуме
    <a href="https://forum.sources.ru/">forum.sources.ru</a>,
    <br>
    либо воспользуйтесь нижеприведенной формой:
</p>

<p>

<FORM name="myform" method=POST onsubmit="return check(this);">
    <table width=400 cellspacing="0" cellpadding=4 bgcolor="#eeeef0">
        <tr>
            <td align="center" colspan=2><span style='color:red'><?php echo $tMessage; ?></span></td>
        </tr>

        <tr>
            <td align="right"><span style='color:red'>*</span> <strong>Ник</strong><br>(или&nbsp;ФИО)</td>
            <td><input type="text" size="32" maxlength="32"
                       name="name" value="<?php echo $name; ?>"></td>
        </tr>
        <tr>
            <td align="right"><span style='color:red'>*</span> <strong>Дата платежа</strong><br>DD.MM.YYYY</td>
            <td><input type="text" size="32" maxlength="32"
                       id="date" name="date" value="<?php echo $date; ?>">
                <img src='html/calendar/calendar.gif' id='calendar_start_time' style='cursor: pointer; border: 0px;'
                     title='Календарь'>
                <script><!--
                    Calendar.setup({
                        inputField: 'date', // id of the input field
                        ifFormat: '%d.%m.%Y', // format of the input field
                        showsTime: false, // will display a time selector
                        button: 'calendar_start_time', // trigger for the calendar (button ID)
                        align: 'Br', // alignment (defaults to 'Bl')
                        singleClick: true,
                        timeFormat: 24,
                        firstDay: 1
                    });//-->
                </script>

            </td>
        </tr>
        <tr>
            <td align="right"><span style='color:red'>*</span><strong>Валюта</strong></td>
            <td><select name="currency">
                    <option value="WMZ">WMZ</option>
                    <option value="WME">WME</option>
                    <option value="WMR">WMR</option>
                    <option value="WMU">WMU</option>
                    <option value="Yandex.Money">Yandex.Money</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="right"><span style='color:red'>*</span> <strong>Сумма</strong></td>
            <td><input type="text" size="32" maxlength="256"
                       name="summ" value="<?php echo $summ; ?>"></td>
        </tr>
        <tr>
            <td align="right"><strong>E-mail&nbsp;&nbsp;</strong></td>
            <td><input type="text" size="32" maxlength="64" name="email"
                       value="<?php echo $email; ?>"></td>
        </tr>
        <tr align="center">
            <td colspan=2>
        <span style='color:red'>*</span> - обязательные для заполнения поля.
            </td>
        </tr>
        <tr align="center">
            <td align="left">
                <input type="button"
                       value="Очистить форму" onClick="return clearall();">
            </td>
            <td align="right">
                &nbsp;&nbsp;&nbsp;
                <input type="submit" value="Сообщить о платеже">
            </td>
        </tr>
    </table>
</form>


</p>


<HR>


<a name=payment></a>

<H2>
    Как перевести деньги
</H2>

<P>
    1) <b>Если у Вас есть электронный кошелек
        в одной из платежных систем:
        <a target="_blank" href="https://www.webmoney.ru">WebMoney</a> или
        <a target="_blank" href="https://money.yandex.ru/">Яндекс.Деньги</a></b>,
    <br>
    то Вы просто перевести деньги на один из указанных выше кошельков
    либо через веб-интерфейс платежной системы, либо с помощью соответствующей
    клиентской программы.
</P>

<P>
    2) <b>Если у Вас есть электронный кошелек в ДРУГИХ платежных системах</b>,
    <BR>
    то Вы можете перевести нам средства с помощью электронных обменных пунктов,
    изобилующих в сети.
    <BR>
    Единственное предостережение - <b>не доверяйте малоизвестным обменникам!</b>
    <BR>
    Лучше уж немного потерять на курсе обмена, чем лишиться всей суммы!
    <BR>
    Приведем лишь несколько известных списков обменных пунктов:
<UL type="square">
    <LI><a target="_blank" href="https://webmoney.ru/rus/cooperation/exchange/onlinexchange/index.shtml">Шлюзы
            ввода/вывода средств между WebMoney Transfer и другими системами</a></LI>
    <LI><a target="_blank" href="https://top.owebmoney.ru/index.php?all=1&cid=1">Рейтинг обменных пунктов на
            owebmoney.ru</a></LI>
    <LI><a target="_blank" href="https://obmenniki.com/">Лучшие обменники. Мониторинг обменных пунктов. Обмен всех
            валют</a></LI>
    <LI><a target="_blank" href="https://cursov.net/">Мониторинг автоматических обменных пунктов. Обмен Яндекс.деньги,
            webmoney - wmz, wmr и прочих систем.</a></LI>
</UL>
</P>

<P>
    3) <b>Если у Вас ВООБЩЕ НЕТ электронных кошельков</b>,
    <BR>
    то при желании Вы можете легко завести себе такой кошелек!
    Для этого достаточно зарегистрироваться на сайте платежной
    системы <a target="_blank" href="https://www.webmoney.ru">WebMoney</a> или
    <a target="_blank" href="https://money.yandex.ru/">Яндекс.Деньги</a>.
    Остается лишь пополнить свой кошелек, и Вы сможете проводить любые
    электронные платежи!
</P>

<P>
    4) <b>Если Вы не хотите (или не имеете возможности) заводить электронные
        кошельки</b>,
    <BR>
    то можно просто сделать перевод обычных денег любым доступным для Вас
    способом (через карточки, банк, платежный терминал, обмен наличных и др.):

<UL type="square">
    <LI><a target="_blank" href="https://money.yandex.ru/help.xml">Что такое Яндекс.Деньги?</a></LI>
    <LI><a target="_blank" href="https://money.yandex.ru/prepaid.xml">Все способы пополнения кошелька Яндекс.деньги</a>
    </LI>
    <LI><a target="_blank" href="https://webmoney.ru/rus/addfunds/">Как пополнить кошелек WebMoney</a></LI>
    <LI><a target="_blank" href="https://webmoney.ru/rus/withdrawfunds/">Как вывести средства WebMoney</a></LI>
    <LI><a target="_blank" href="https://geo.webmoney.ru/aspx/GeoMain.aspx">Территория WebMoney - перечень обменных
            пунктов в 57 странах мира</a></LI>
</UL>
</P>


<HR>


<a name=prize></a>

<H2>
    Призы для проведения конкурсов и викторин
</H2>

<P>
    Мы постоянно проводим всевозможные игры, конкурсы и викторины
    на нашем форуме. Интерес наших посетителей к таким мероприятиям достаточно
    высок, но если бы была возможность поощрения победителей и активных участников
    более материальными призами, нежели только рейтинг и признание,
    то интерес аудитории мог бы значительно возрасти.
</P>

<P>
    Если Вы или Ваша организация готовы предоставить нашему проекту
    свою продукцию или услуги в качестве призов, мы с благодарностью
    задействуем их в проведении наших мероприятий.
    Такое участие с Вашей стороны не только повысит интерес наших участников,
    но и сможет способствовать дополнительной популяризации брендов Вашей
    организации среди профильной аудитории.
</P>

<P>
    По всем вопросам обращайтесь к Валерию Вотинцеву:
    <BR>
    E-mail:

    <A href="mailto:stop@spam.net"
       onmouseover="this.href='mai'+'lto:'+'vot'+'%40'+'sources.ru?Subject=Project Donation'">
        stop@spam.net</A>

    <BR>
    ICQ: 8754415
    <BR>
    Телефон: 8 (910) 400-3246

</P>


</body>
</html>
