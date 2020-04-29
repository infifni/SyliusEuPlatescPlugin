@paying_with_euplatesc_for_order
Feature: Paying with EuPlatesc during checkout
    In order to buy products
    As a Customer
    I want to be able to pay with EuPlatesc

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "contact@infifnisoftware.ro" identified by "password123456"
        And the store has a payment method "EuPlatesc" with a code "euplatesc" and EuPlatesc Checkout gateway
        And the store has a product "Married to PHP" priced at "$23.99"
        And the store ships everywhere for free
        And I am logged in as "contact@infifnisoftware.ro"

    @ui
    Scenario: Successful payment
        Given I added product "Married to PHP" to the cart
        And I have proceeded selecting "EuPlatesc" payment method
        When I confirm my order with EuPlatesc payment
        And I sign in to EuPlatesc and pay successfully
        Then I should be notified that my payment has been completed

    @ui
    Scenario: Cancelling the payment
        Given I added product "Married to PHP" to the cart
        And I have proceeded selecting "EuPlatesc" payment method
        When I confirm my order with EuPlatesc payment
        And I cancel my EuPlatesc payment
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again

    @ui
    Scenario: Retrying the payment with success
        Given I added product "Married to PHP" to the cart
        And I have proceeded selecting "EuPlatesc" payment method
        And I have confirmed my order with EuPlatesc payment
        But I have cancelled EuPlatesc payment
        When I try to pay again with EuPlatesc payment
        And I sign in to EuPlatesc and pay successfully
        Then I should be notified that my payment has been completed
        And I should see the thank you page

    @ui
    Scenario: Retrying the payment and failing
        Given I added product "Married to PHP" to the cart
        And I have proceeded selecting "EuPlatesc" payment method
        And I have confirmed my order with EuPlatesc payment
        But I have cancelled EuPlatesc payment
        When I try to pay again with EuPlatesc payment
        And I cancel my EuPlatesc payment
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again

    @ui
    Scenario: Get a notification of a successful payment
        Given I added product "Married to PHP" to the cart
        And I have proceeded selecting "EuPlatesc" payment method
        When I confirm my order with EuPlatesc payment
        Then I should get a notification of a successful transaction
        And Payment status should have been completed