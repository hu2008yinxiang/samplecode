<?php
namespace Cmds;

class SearchFriendsCmd extends Cmd
{

    const CMD_NAME = 'SearchFriends';

    const MAX_PAGE = 6;

    const PAGE_ITEM = 10;

    public static function defaultData()
    {
        return array(
            'keyword' => '',
            'page' => 1
        );
    }

    protected function do_execute()
    {
        if (! \SessionManager::checkIP()) {
            $this->ret['result']['errno'] = \Errors::QUOTA_LIMITED;
            return;
        }
        $keyword = trim($this->data['keyword']);
        $page = $this->data['page'];
        if ($page < 1)
            $page = 1;
        if (empty($keyword)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        $data = array();
        // email
        $is_email = false;
        $is_id = false;
        if (strpos($keyword, '@') != false) {
            $is_email = true;
        }
        // id
        if (intval($keyword)) {
            $is_id = true;
        }
        $cond = '';
        if ($is_id && $is_email) {
            //
            $cond = 'account_id = :keyword: OR bind_email = :keyword:';
        } elseif ($is_email) {
            //
            $cond = 'bind_email = :keyword:';
        } elseif ($is_id) {
            //
            $cond = 'account_id = :keyword:';
        } else {
            //
            $cond = ':keyword: <> :keyword:';
        }
        $ua = \UserAccounts::findFirst(array(
            $cond,
            'bind' => array(
                'keyword' => $keyword
            )
        ));
        if ($ua) {
            $this->ret['result']['account_id'] = $ua->account_id;
            $this->ret['result']['nickname'] = $ua->nickname;
            $this->ret['result']['photo'] = $ua->photo;
            $this->ret['result']['bind_email'] = $ua->bind_email;
            return;
        }
        $this->ret['result']['errno'] = \Errors::USER_NOT_FOUND;
        return;
        // nickname
        $uas = \UserAccounts::find(array(
            'nickname = :keyword:' . ($is_email ? ' OR bind_email = :keyword:' : '') . ($is_id ? ' OR account_id = :keyword:' : ''),
            'bind' => array(
                'keyword' => $keyword
            ),
            'limit' => static::PAGE_ITEM * static::MAX_PAGE
        ));
        $paginator = new \Phalcon\Paginator\Adapter\Model(array(
            'data' => $uas,
            'limit' => static::PAGE_ITEM,
            'page' => $page
        ));
        $page = $paginator->getPaginate();
        $uas = $page->items;
        foreach ($uas as $ua) {
            $data[] = $ua->toArray(array(
                'account_id',
                'nickname',
                'bind_email'
            ));
        }
        $this->ret['result']['data'] = $data;
        $this->ret['result']['currentPage'] = $page->current;
        $this->ret['result']['nextPage'] = $page->next;
        $this->ret['result']['pageCount'] = $page->total_pages;
        $this->ret['result']['itemCount'] = $page->total_items;
    }
}