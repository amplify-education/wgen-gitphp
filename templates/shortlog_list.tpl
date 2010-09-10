{foreach from=$revlist item=rev}
  <tr class="{cycle values="light,dark"}">
    <td title="{if $rev->GetAge() > 60*60*24*7*2}{$rev->GetAge()|agestring}{else}{$rev->GetCommitterEpoch()|date_format:"%F"}{/if}"><em>{if $rev->GetAge() > 60*60*24*7*2}{$rev->GetCommitterEpoch()|date_format:"%F"}{else}{$rev->GetAge()|agestring}{/if}</em></td>
    <td><em>{$rev->GetAuthorName()}</em></td>
    <td>
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$rev->GetHash()}" class="list" {if strlen($rev->GetTitle()) > 50}title="{$rev->GetTitle()}"{/if}><strong>{$rev->GetTitle(50)}</strong></a>
  <span class="refs">
  {foreach from=$rev->GetHeads() item=revhead}
    <span class="head">
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/heads/{$revhead->GetName()}">{$revhead->GetName()}</a>
    </span>
  {/foreach}
  {foreach from=$rev->GetTags() item=revtag}
    <span class="tag">
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$revtag->GetName()}">{$revtag->GetName()}</a>
    </span>
  {/foreach}
  </span>
    </td>
    <td class="link"><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$rev->GetHash()}">commit</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$rev->GetHash()}">commitdiff</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&h={$rev->GetHash()}&hb={$rev->GetHash()}">tree</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=snapshot&h={$rev->GetHash()}">snapshot</a>
    {if $mark != $rev->GetHash()}
    | <a href="{$rev->GetMarkUrl()}">Select for diff</a>
    {else}
    | <a href="{$rev->GetMarkUrl(true)}">Unselect for diff</a>
    {/if}
    {if $mark && $mark != $rev->GetHash()}
    | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$rev->GetHash()}&hp={$mark}">Diff against selected (<abbr title="{$mark}">{$mark_abbr}</abbr>)</a>
    {/if}
    </td>
  </tr>
{/foreach}
