<?php

namespace App\Core\Security;

use App\Core\BaseController;
use App\Core\HTTP\Request;
use App\Entities\User;
use App\Core\Database\QueryBuilder;
use Exception;

class TokenController extends BaseController
{
    private SecurityController $securityController;


    public function __construct()
    {
        parent::__construct();
        $this->securityController = new SecurityController();
    }


    public function generate(User $user, string $type, int $length = 64, int $duration = 3600*24): string
    {
        $this->deleteByUser($user, $type);

        $token = new Token();
        $token->setUser($user);
        $token->setType($type);
        $token->setHash($this->securityController->createHash($length));
        $token->setExpireAt(date('Y-m-d H:i:s', time()+$duration));
        $token->save();

        return $token->getHash();
    }


    public function deleteByUser(User $user, ?string $type = null): void
    {
        $q = new QueryBuilder();
        if ($type === null) {
            $q->delete('tokens')->where('id_user = '.$user->getId())->execute();
        } else {
            $q->delete('tokens')->where('id_user = '.$user->getId(), 'type = "'.$type.'"')->execute();
        }
    }


    /**
     * @return bool|mixed
     */
    public function findActiveByType(string $type, User $user)
    {
        $q = new QueryBuilder();
        return $q->select()
            ->from('tokens')
            ->where('type = "'.$type.'"', 'id_user = '.$user->getId(), 'expire_at > NOW()')
            ->limit(1)
            ->execute(get_class(new Token()));
    }


    /**
     * @throws Exception
     */
    public function checkToken(string $type): bool
    {
        $request = new Request();
        if ($request->isGetExists('id') && $request->isGetExists('token')) {
            $user = new User();
            $user = $user->find($request->getGetData('id'));

            $token = $this->findActiveByType($type, $user);
            return $token && $token->getHash() === trim($request->getGetData('token'), '/') && !$token->isExpired();
        } else {
            return false;
        }
    }
}
