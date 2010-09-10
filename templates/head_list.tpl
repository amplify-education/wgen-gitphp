{foreach from=$headlist item=head}
  <tr class="{cycle name=heads values="light,dark"}">
    {assign var=headcommit value=$head->GetCommit()}
    <td><em>{$headcommit->GetAge()|agestring}</em></td>
    <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/heads/{$head->GetName()}" class="list"><strong>{$head->GetName()}</strong></td>
    <td class="link"><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/heads/{$head->GetName()}">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=log&h=refs/heads/{$head->GetName()}">log</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&h=refs/heads/{$head->GetName()}&hb={$headcommit->GetHash()}">tree</a>
      {if $mark != $headcommit->GetHash()}
        | <a href="{$headcommit->GetMarkUrl()}">Select for diff</a>
      {else}
        | <a href="{$headcommit->GetMarkUrl(true)}">Unselect for diff</a>
      {/if}
      {if $mark && $mark != $headcommit->GetHash()}
        | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$headcommit->GetHash()}&hp={$mark}">Diff against selected (<abbr title="{$mark}">{$mark_abbr}</abbr>)</a>
      {/if}
    </td>
  </tr>
{/foreach}

