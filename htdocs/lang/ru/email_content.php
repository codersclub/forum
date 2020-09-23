<?php


$EMAIL['header'] = <<<EOF

EOF;

$EMAIL['footer'] = <<<EOF
С уважением,
<#BOARD_NAME#>.
<#BOARD_ADDRESS#>

EOF;

$SUBJECT['pm_notify'] = "Приватное сообщение от <#POSTER#>";

$EMAIL['pm_notify'] = <<<EOF

Внимание, это уведомление от робота!
ОТВЕЧАТЬ НА ЭТО ПИСЬМО НЕ НУЖНО!
Поскольку робот не любит читать письма ;)
-----------------------------------------

<#NAME#>,

<#POSTER#> отправил Вам приватное сообщение:
Subject: "<#TITLE#>".

<#MSG_BODY#>

-------
Ссылка для просмотра сообщения на сайте:
<#BOARD_ADDRESS#><#LINK#>


EOF;

$EMAIL['send_text'] = <<<EOF
Возможно, Вас заинтересует эта тема: <#THE LINK#>

С уважением,
<#USER NAME#>

EOF;

$EMAIL['report_post'] = <<<EOF
<#MOD_NAME#>,

Письмо отправлено от <#USERNAME#> через ссылку "Сообщить модератору".

------------------------------------------------
Тема: <#TOPIC#>
Ссылка на сообщение: <#LINK_TO_POST#>
Сообщение пользователя:

<#REPORT#>
------------------------------------------------

EOF;

$EMAIL['pm_archive'] = <<<EOF
<#NAME#>,
Это письмо отправлено с <#BOARD_ADDRESS#>.

Все ваши архивированные сообщения сжаты и прикреплены к этому письму.

EOF;

$EMAIL['reg_validate'] = <<<EOF
Здравствуйте!

Вы получили это письмо в связи с регистрацией данного email 
на форуме <#BOARD_ADDRESS#>.

Указанные при регистрации данные:
Логин: <#NAME#>
E-mail: <#EMAIL#>
IP-адрес: <#IP_ADDRESS#>

Если Вы не регистрировались на нашем форуме, 
просто проигнорируйте это письмо и удалите его.
Больше Вы не получите такого письма.

------------------------------------------------
Инструкция по активации
------------------------------------------------

Мы просим Вас подтвердить регистрацию, чтобы проверить,
что введённый вами е-мэйл - реальный.
Это требуется для защиты от спама и т.п..

Подтвердив Вашу регистрацию, Вы соглашаетесь с правилами общения в форуме, 
указанными в конце этого письма.

Если Вы не согласны с данными правилами, просто не подтверждайте Вашу 
регистрацию! Неподтвержденная регистрация будет удалена через 3 дня.

Для активации вашего аккаунта зайдите по следующей ссылке:

<#THE_LINK#>

(Пользователям AOL потребуется скопировать эту ссылку и вставить 
в адресную строку браузера).


Не сработало?

Если у Вас ничего не получилось, и Вы не смогли подтвердить свою регистрацию, 
зайдите по следующей ссылке:

<#MAN_LINK#>

и введите нижеприведенные ID пользователя и ключ подтверждения:

ID пользователя: <#ID#>

Ключ подтверждения: <#CODE#>

Произведите действия "копировать"/"вставить" или введите эти данные вручную 
в соответствующие поля.

Если и сейчас ничего не получилось - возможно, ваш аккаунт уже удалён.
В этом случае обратитесь к администратору для разрешения проблемы.

Благодарим вас за регистрацию!

--------------------------------------------
ПРАВИЛА ФОРУМА
--------------------------------------------

В любом обществе существуют свои правила поведения. Наш форум не исключение, и является сообществом общения людей по интересам. Все участники должны неукоснительно соблюдать не только общечеловеческие нормы, но и правила, присущие для общения внутри форума:

1. Запрещается использовать ненормативную лексику.
2. Запрещается создание пустых сообщений, спама, преднамеренной рекламы, сообщений, лишённых смысла, не имеющих смысловой нагрузки (флуд). Запрещается распространение заведомо ложной информации.
3. Для создания своего топика вы должны выбрать тематический подфорум, который прямо соответствует теме Вашего вопроса. Темы должны создаваться в конференции именно так, а не по принципу "постю туда, где больше народа тусуется". Такие темы будут переноситься модератором, а при постоянных нарушениях со стороны определенного человека, в отношении его будут применены наказания. Если Вы затрудняетесь определить для своего вопроса требуемый тематический подфорум, занесите свой вопрос, создав тему в подфоруме "Общие вопросы", если такой подфорум предусмотрен в выбранном разделе. Модератор перенесёт Вашу тему в нужную конференцию.
4. Запрещается создавать одну и ту же тему в нескольких подфорумах. Такое явление будет рассматриваться как спам, и соответственно спаму, все повторяющиеся в разных подфорумах темы будут удалены.
5. Сообщения или даже полностью топики будут безжалостно удаляться в том случае, если общение в них переходит в личные оскорбления. Всегда соблюдайте такт, вы общаетесь в культурном обществе.
6. За порядком в конференции следят модераторы и администраторы. Модераторы осуществляют контроль над отдельными форумами, могут редактировать, удалять сообщения, закрывать темы только в своих форумах. Администраторы имеют все права модераторов в любых форумах.
7. Название топика должно соответствовать теме вопроса, в нём содержащегося. Модератор оставляет за собой право удалять топики с подобными темами: "Спасите!", "Помогите!", "Ничего не получается!", "Горю!", "Срочно!" и т.п.
8. Если Вы ошиблись, например, сдублировали сообщение, отправили случайно ответ не туда или отправили пустое сообщение, то если Вы не зарегистрированы, оставьте всё как есть. Читатели разберутся, модератор потом почистит. Если очень хотите обратить на это его внимание, отправьте ему письмо. Не стоит писать новые сообщения и комментировать свои ошибки, да еще потом писать сообщения, в которых извиняться за такой флуд. Если же Вы зарегистрированы, то Вы сами можете удалить своё сообщение или его исправить. Для удаления ошибочного топика, сообщите об этом модератору раздела.
9. Если вы затрудняетесь в использовании форума, обратитесь к помощи по форуму, щёлкнув линк https://forum.sources.ru/index.php?s=8cc0b7937c380d394851fdbf06dee9df&act=Help, расположенный вверху форума или обратитесь в соответствующую конференцию (https://forum.sources.ru/index.php?s=8cc0b7937c380d394851fdbf06dee9df&showforum=7). Также Вы можете обратиться к модератору конференции или к администратору.
10. Если Вы обнаружили, что общение в ветке нарушает данные правила, сообщите об этом модератору конференции, воспользовавшись кнопкой "Report", не отвечайте грубостью на грубость. Кнопку "Report" разрешено использовать только для этого. Запрещается использовать эту возможность для привлечения внимания модератора к Вашему вопросу.
11. Если Вы здесь недавно, не обольщайтесь тоном некоторых дискуссий. Многие люди давно знакомы и могут позволить себе такой тон по отношению друг к другу.
12. Запрещается обсуждение модераторов и политики модерирования. В публичной конференции не принято обращаться к модератору по тому или иному вопросу прямо в форуме, не смотря на любые его действия. Все вопросы к модератору - персональным сообщением или через электронную почту. Если у Вас есть какие-то претензии по модерированию Ваших сообщений, обратитесь к вышестоящему члену форума по электронной почте или персональному сообщению. Также Вы можете использовать электронный адрес admin@sources.ru
13. За нарушения данных правил модератор конференции или администратор может применить к Вам наказание. Самой лёгкой формой наказаний являются предупреждения. Фактически, уровень предупреждений - это характер Вашего поведения на форуме. Уровень Ваших предупреждений виден в Вашем профиле или в соответствующем линке "Предупреждения" под ником. Уровень предупреждений может быть и уменьшен, если Вы что-то сделаете полезное для нашего сообщества. Если какой-то член форума нарушает правила форума неоднократно или имеет уже предупреждения модератор конференции или администратор имеет право наложить более серьёзные наказания, такие как: режим Read Only (лишение права ответов - вы сможете только читать форумы), предварительная проверка любых сообщений провинившегося (премодерация), лишение любых прав (Вы не сможете посещать конференцию - бан). 
Взыскания могут быть наложены на любой срок по усмотрению модератора или администратора. Если Вы думаете, что, взыскание на Вас наложено незаконно, Вы можете обратиться к вышестоящему члену форума с данным вопросом. Взыскания обязательно накладываются с уточнением причины, Вы всегда можете посмотреть причину, кликнув на процент предупреждений в Вашем профайле или в линке "Предупреждения" под ником.
14. Не следует использовать форум для личного общения. Для личного общения используйте персональные сообщения (кнопочка PM под ником) или электронную почту.
15. Во избежание споров и неудовольствий пишите на русском языке. Если русская раскладка Вам недоступна, используйте латиницу, однако имейте в виду, что на латинице чтение сообщений затруднительно, что обязательно скажется на посещении и внимании к Вашей теме другими членами форума.
16. Если именно Ваши топики непопулярны или члены форума не спешат Вам помочь, задумайтесь, а не в Вас ли дело? Настоятельно рекомендуем прочитать Вам и всем остальным статью "Как правильно задавать вопросы" (https://forum.sources.ru/index.php?showtopic=2400).
17. Модератор это не бот, это тоже человек, имеющий свои принципы, эмоции, амбиции, взгляды на жизнь, плохое или хорошее настроение. Хотя модератор и стремится быть нейтральным, чтобы подходить к любой ситуации объективно, всё-таки не стоит испытывать его терпение, потому что в любом случае модератор не пострадает ;). Модератор - это существо загадочное и непредсказуемое, может быть шёлковым, а может быть и тираном (особенно, когда с похмелья), поэтому берегите и не обижайте его!

Данные правила весьма просты, и мы не думаем, что кто-нибудь когда-нибудь будет о них задумываться, потому, как мы все живём в культурном обществе. Однако по отношению к злостным нарушителям будут применяться самые экстраординарные меры.
EOF;

$EMAIL['admin_newuser'] = <<<EOF
Здравствуйте, уважаемый администратор!

На вашем форуме зарегистрировался новый пользователь <#MEMBER_NAME#> (<#DATE#>)

Вы можете отключить это уведомление через админ-центр.

Доброго вам дня!

EOF;

$EMAIL['lost_pass'] = <<<EOF
<#NAME#>,
Это письмо отправлено из <#BOARD_ADDRESS#>.

Вы получили это письмо, в связи с запросом на восстановление 
забытого пароля из <#BOARD_NAME#>.

------------------------------------------------
ВАЖНО!
------------------------------------------------

Если Вы не делали запроса на изменение пароля, проигнорируйте и немедленно удалите это 
письмо. Продолжайте только в том случае, если Вам действительно требуется восстановление пароля!

------------------------------------------------
Инструкция по активации ниже.
------------------------------------------------

Мы требуем от Вас "подтверждения" Вашего запроса на восстановление забытого пароля
для проверки того, что это действие выполнено именно Вами. Это требуется для защиты от 
нежелательных злоупотреблений.

Зайдите по нижеуказанной ссылке и заполните остальные поля формы

<#THE_LINK#>

(Пользователям AOL E-mail, потребуется скопировать эту ссылку и вставить в адресную строку 
браузера).

------------------------------------------------
Не сработало?
------------------------------------------------

Если Вам не удалось активировать Вашу регистрацию, зайдите по ссылке

<#MAN_LINK#>

Там Вы должны будете ввести ID пользователя и ключ подтверждения. Ниже указаны эти 
данные:

ID пользователя: <#ID#>

Ключ подтверждения: <#CODE#>

Произведите действия Копировать/Вставить или введите эти данные вручную, в соответствующие 
поля формы.

------------------------------------------------
Не сработало?
------------------------------------------------

Если Ваша переактивация не получается, возможно Ваш аккаунт удалён или Вы не прошли процесс предыдущей
активации, например при регистрации или изменении e-mail адреса. В этом случае, завершите предыдущую активацию. При продолжении/возникновении дальнейших ошибок, попробуйте обратиться к Администратору, для разрешения проблемы.

IP адрес отправителя: <#IP_ADDRESS#>


EOF;

$EMAIL['newemail'] = <<<EOF
<#NAME#>,
Это письмо отправлено из <#BOARD_ADDRESS#>.

Вы получили это письмо, в связи с изменением e-mail адреса.

------------------------------------------------
Инструкция по активации ниже
------------------------------------------------

Мы требуем от Вас "подтверждения" изменения Вашего e-mail адреса, для проверки того, что это 
действие выполнено именно Вами. Это требуется для защиты от нежелательных 
злоупотреблений и спама.

Для активации аккаунта зайдите по следующей ссылке:

<#THE_LINK#>

(Пользователям AOL E-mail, потребуется скопировать эту ссылку и вставить в адресную строку 
браузера).

------------------------------------------------
Не сработало?
------------------------------------------------

Если Вам не удалось активировать Вашу регистрацию, зайдите по ссылке

<#MAN_LINK#>

Там Вы должны будете ввести ID пользователя и ключ подтверждения. Ниже указаны эти 
данные:

ID пользователя: <#ID#>

Ключ подтверждения: <#CODE#>

Произведите действия Копировать/Вставить или введите эти данные вручную, в соответствующие 
поля формы.

После завершения активации, необходимо переавторизоваться, для обновления Ваших данных.

------------------------------------------------
Помогите! Я получаю ошибку!
------------------------------------------------

Если Ваша переактивация не получается, возможно Ваш аккаунт удалён или Вы не прошли процесс предыдущей
активации, например при регистрации или изменении e-mail адреса. В этом случае, завершите предыдущую активацию. При продолжении/возникновении дальнейших ошибок, попробуйте обратиться к Администратору, для разрешения проблемы.


EOF;

$EMAIL['forward_page'] = <<<EOF
<#TO_NAME#>

<#THE_MESSAGE#>

---------------------------------------------------
Примечание:
<#BOARD_NAME#> не несёт никакой ответственности
за содержание этого письма.

EOF;

$SUBJECT['subs_with_post'] = "Новый ответ в теме &quot;<#TITLE#>&quot; от <#POSTER#>";

$EMAIL['subs_with_post'] = <<<EOF
<#NAME#>,

<#POSTER#> ответил в теме "<#TITLE#>", на которую вы подписаны.

----------------------------------------------------------------------
<#POST#>
----------------------------------------------------------------------

Тема находится здесь:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>&view=getnewpost

Возможно в теме больше одного ответа, но вам будет отправлено только одно письмо после каждого посещения темы, на которую вы подписаны. Это является пределом на количество отправленных вам писем.

Отписка:
--------------

Вы можете отписаться от темы в любое время, через Ваш профиль, кликнув по ссылке "Мои подписки".

EOF;

$SUBJECT['subs_new_topic'] = "Уведомление о новой теме в форуме";

$EMAIL['subs_new_topic'] = <<<EOF
<#NAME#>,

<#POSTER#> создал новую тему с заголовком "<#TITLE#>" в форуме "<#FORUM#>".

Тема находится здесь:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>

Если вы хотите получать уведомления об ответах в эту тему, вы должны кликнуть 
по ссылке "Подписка на тему", находящейся на странице самой темы или посетив нижеуказанную ссылку:
<#BOARD_ADDRESS#>?act=Track&f=<#FORUM_ID#>&t=<#TOPIC_ID#>


Отписка

Вы можете отписаться от уведомлений по теме в любое время, через ваш профиль, кликнув по ссылке "Мои уведомления".

EOF;

$SUBJECT['subs_no_post'] = "Новый ответ в теме &quot;<#TITLE#>&quot; от <#POSTER#>";

$EMAIL['subs_no_post'] = <<<EOF
<#NAME#>,

<#POSTER#> ответил в теме "<#TITLE#>", на которую вы подписаны.

Тема находится здесь:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>&view=getnewpost

Возможно в теме больше одного ответа, но только одно письмо будет отправлено вам после каждого посещения темы, на которую вы подписаны. Это является пределом на количество отправленных вам писем.

Отписка:

Вы можете отписаться от темы в любое время, через ваш профиль, кликнув по ссылке "Мои уведомления".

EOF;

$EMAIL['email_member'] = <<<EOF
<#MEMBER_NAME#>,

<#FROM_NAME#> отправил вам это письмо с <#BOARD_ADDRESS#>.


<#MESSAGE#>

---------------------------------------------------
<#BOARD_NAME#> не несёт ответственности за содержание этого письма.


EOF;

$EMAIL['complete_reg'] = <<<EOF
Поздравляем!

Администратор подтвердил вашу регистрацию или запрос на изменение e-mail
в <#BOARD_NAME#>. Теперь Вы можете авторизоваться с вашими данными
и получить полный доступ к <#BOARD_ADDRESS#>

EOF;
