{*
 *  history.tpl
 *  gitphp: A PHP git repository browser
 *  Component: History view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Page header *}
 <div class="page_nav">
   {include file='project_header.tpl' unselect='history' commit=$commit}
   <br /><br />
 </div>

 {include file='title.tpl' titlecommit=$commit}

 {include file='path.tpl' pathobject=$blob target='blob'}
 
 <table cellspacing="0">
   {* Display each history line *}
   {foreach from=$blob->GetHistory() item=historyitem}
     {assign var=historycommit value=$historyitem->GetCommit()}
     <tr class="{cycle values="light,dark"}">
       <td title="{if $historycommit->GetAge() > 60*60*24*7*2}{$historycommit->GetAge()|agestring}{else}{$historycommit->GetCommitterEpoch()|date_format:"%F"}{/if}"><em>{if $historycommit->GetAge() > 60*60*24*7*2}{$historycommit->GetCommitterEpoch()|date_format:"%F"}{else}{$historycommit->GetAge()|agestring}{/if}</em></td>
       <td><em>{$historycommit->GetAuthorName()}</em></td>
       <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$historycommit->GetHash()}" class="list" {if strlen($historycommit->GetTitle()) > 50}title="{$historycommit->GetTitle()}"{/if}><strong>{$historycommit->GetTitle(50)}</strong></a>
       <span class="refs">
       {foreach from=$historycommit->GetHeads() item=historyhead}
         <span class="head">
	   <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/heads/{$historyhead->GetName()}">{$historyhead->GetName()}</a>
	 </span>
       {/foreach}
       {foreach from=$historycommit->GetTags() item=historytag}
         <span class="tag">
	   <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$historytag->GetName()}">{$historytag->GetName()}</a>
	 </span>
       {/foreach}
       </span>
       </td>
       <td class="link"><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$historycommit->GetHash()}">commit</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$historycommit->GetHash()}">commitdiff</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blob&hb={$historycommit->GetHash()}&f={$blob->GetPath()}">blob</a>{if $blob->GetHash() != $historyitem->GetToHash()} | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blobdiff&h={$blob->GetHash()}&hp={$historyitem->GetToHash()}&hb={$historycommit->GetHash()}&f={$blob->GetPath()}">diff to current</a>{/if}
       </td>
     </tr>
   {/foreach}
 </table>

 {include file='footer.tpl'}

