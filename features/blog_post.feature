Feature: Maintain blog posts

  @createSchema
  Scenario: Create a blog post
    Given I am authenticated as "admin"
    When  I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/blog_posts" with body:
    """
    {
      "title": "Hello a title very very small",
      "content": "The content is suppose to be at least 20 characters",
      "slug": "a-new-slug"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON matches expected template:
    """
    {
       "@context": "/api/contexts/BlogPost",
       "@id": @string@,
       "@type": "BlogPost",
       "title": "Hello a title very very small",
       "published": "@string@.isDateTime()",
       "content": "The content is suppose to be at least 20 characters",
       "author": {
          "@id":"/api/users/1",
          "@type":"User",
          "name":"Pasha",
          "email":"admin@email.com",
          "roles":["ROLE_ADMIN"]
        },
       "slug": "a-new-slug",
       "images": []
    }
    """

  Scenario: Add comment to the new blog post
    Given I am authenticated as "admin"
    When  I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/comments" with body:
    """
    {
      "content": "The comment",
      "post": "/api/blog_posts/51"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON matches expected template:
    """
    {
      "@context": "/api/contexts/Comment",
      "@id": @string@,
      "@type": "Comment",
      "content": "The comment",
      "published": "@string@.isDateTime()",
      "author": {
          "@id": "/api/users/1",
          "@type": "User",
          "username": "admin",
          "name": "Pasha",
          "email": "admin@email.com",
          "roles": ["ROLE_ADMIN"]
      }
    }
    """

  @createSchema
  Scenario: Throw exception for invalid post
    Given I am authenticated as "admin"
    When  I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/blog_posts" with body:
    """
    {
      "title": "",
      "content": "",
      "slug": "a-new-slug"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON matches expected template:
    """
    {
      "@context": "/api/contexts/ConstraintViolationList",
      "@type": "ConstraintViolationList",
      "hydra:title": "An error occurred",
      "hydra:description": "title: This value should not be blank.\ncontent: This value should not be blank.\ncontent: This value is too short. It should have 20 characters or more.",
      "violations": [
          {
              "propertyPath": "title",
              "message": "This value should not be blank."
          },
          {
              "propertyPath": "content",
              "message": "This value should not be blank."
          },
          {
              "propertyPath": "content",
              "message": "This value is too short. It should have 20 characters or more."
          }
      ]
    }
    """

  @createSchema
  Scenario: Throw exception for non-authorized user
    When  I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/blog_posts" with body:
    """
    {
      "title": "",
      "content": "",
      "slug": "a-new-slug"
    }
    """
    Then the response status code should be 401
