<?php

use App\DataFixtures\AppFixtures;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Coduo\PHPMatcher\Factory\MatcherFactory;
use Coduo\PHPMatcher\Matcher;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class FeatureContext extends RestContext
{
    const USERS = [
        'admin' => 'admin',
    ];
    const AUTH_URL = '/api/login_check';
    const AUTH_JSON = '
        {
            "username": "%s",
            "password": "%s"
        }
    ';

    private AppFixtures $fixtures;
    private Matcher $matcher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Request $request,
        AppFixtures $fixtures,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($request);
        $this->fixtures      = $fixtures;
        $this->matcher       =
            (new MatcherFactory())->createMatcher();
        $this->entityManager = $entityManager;
    }

    /**
     * @Given I am authenticated as :user
     */
    public function iAmAuthenticatedAs($user)
    {
        $this->request->setHttpHeader('Content-Type', 'application/ld+json');
        $this->request->send(
            'POST',
            $this->locatePath(self::AUTH_URL),
            [],
            [],
            sprintf(self::AUTH_JSON, $user, self::USERS[$user])
        );
        $json = json_decode($this->request->getContent(), true);

        // Reassure that the token has been provided
        $this->assertTrue(isset($json['token']));

        $token = $json['token'];

        $this->request->setHttpHeader(
            'Authorization',
            "Bearer $token"
        );
    }

    /**
     * @Then the JSON matches expected template:
     */
    public function theJsonMatchesExpectedTemplate(PyStringNode $json)
    {
        $actual = $this->request->getContent();
        $this->assertTrue(
            $this->matcher->match($actual, $json->getRaw())
        );
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createSchema()
    {
        $classes    = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $purger          = new ORMPurger($this->entityManager);
        $fixtureExecutor = new ORMExecutor(
            $this->entityManager,
            $purger
        );

        $fixtureExecutor->execute(
            [
                $this->fixtures,
            ]
        );
    }

    /**
     * @BeforeScenario @image
     */
    public function prepareImages()
    {
        copy(
            __DIR__.'/../fixtures/abc.jpg',
            __DIR__.'/../fixtures/files/abc.jpg'
        );
    }
}
