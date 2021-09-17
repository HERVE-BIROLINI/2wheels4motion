<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager
                                , UrlGeneratorInterface $urlGenerator
                                , CsrfTokenManagerInterface $csrfTokenManager
                                , UserPasswordEncoderInterface $passwordEncoder
                                )
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    // ETAPE 1 du Submit (POST) du formulaire Login
    public function supports(Request $request)
    {
        // dd('supports (49) => $request = '.$request);
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    // ETAPE 2 du Submit (POST) du formulaire Login
    // ... récupère les valeurs du formulaire (POST)
    public function getCredentials(Request $request)
    {
        // dd('getCredentials (58) => $request = '.$request);
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        // dd($credentials['csrf_token']);
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    // ETAPE 3 du Submit (POST) du formulaire Login
    // ... récupère l'enregistrement du User
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // dd('getUser (77) => $credentials = '.$credentials.' / $userProvider ...');
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        // dd('getUser (84) => $user = '.$user);

        if (!$user) {
            // dd('getUser (87 => $user introuvable !');
            throw new UsernameNotFoundException('Adresse électronique introuvable.');
        }

        // dd('getUser (91) => $user Trouvé !');
        return $user;
    }

    // ETAPE 4 du Submit (POST) du formulaire Login
    // ... User trouvé, vérifie le mot de passe...
    //  $credentials = Objet contenant les données de la Form (login)
    //  $user = User enregistré pour l'adresse email saisie dans la Form (login)
    public function checkCredentials($credentials, UserInterface $user)
    {
        // dd(/*'checkCredentials (101) => $credentials = '.*/$credentials);
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    // ETAPE 5 du Submit (POST) du formulaire Login
    // ... User trouvé, extrait le mot de passe (avant Hashage)...
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        // Quand ? D'où ? Pour passer ici ?
        // dd(/*'getPassword (113) => $credentials = '.*/$credentials);
        return $credentials['password'];
    }

    // ETAPE 6 du Submit (POST) du formulaire Login
    //      OU
    // ETAPE 1 de la confirmation du compte par le mail
    // ... User trouvé, mot de passe (avant Hashage)...
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            // ETAPE 1 : Authentification de l'adresse email, via lien du mail de confirmation
            // dd('onAuthenticationSuccess (125) => $targetPath = '.$targetPath);

            return new RedirectResponse($targetPath);
        }

        // ETAPE 6 : Passage consécutif aussi bien à REGISTER qu'à LOGIN...
        // ... Mot de passe valide...
        // dd('onAuthenticationSuccess (132) => En dehors du IF / $providerKey = '.$providerKey);

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // $this->urlGenerator->generate('homepage');
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    // ETAPE 3.2 du Submit (POST) du formulaire Login
    // ... adresse email introuvable dans la BdD
    //  => retourne à la page Login
    protected function getLoginUrl()
    {
        // dd('getLoginUrl (144)');
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
