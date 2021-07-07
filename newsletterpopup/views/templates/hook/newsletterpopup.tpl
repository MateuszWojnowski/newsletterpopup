<div id="newsletter_popup">
    <div class="close"">x</div>
    <div class="title">
        {if isset($newsletterpopup_title) && $newsletterpopup_title}
            {$newsletterpopup_title}
        {else}
            Zapisz się na newsletter!
        {/if}
    </div>
    <div class="content">
        {if isset($newsletterpopup_content) && $newsletterpopup_content}
            {$newsletterpopup_content}
        {else}
            Dzięki temu nigdy nie ominą Cię wyjątkowe okazje
        {/if}
    </div>
    <form class="form" action="" method="post" >
        <input class="input_text" name="name" type="text" placeholder="Imię"/> </br>
        <input class="input_text" name="email" type="text" placeholder="E-mail"/> </br>
        <input class="submit" type="submit" value="{$newsletterpopup_submit}" onclick="removePopup()">
    </form>
</div>