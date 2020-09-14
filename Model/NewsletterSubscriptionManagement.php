<?php
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 * @author marcel.hauri@staempfli.com
 */

namespace Staempfli\CheckoutNewsletterSubscription\Model;

use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use Staempfli\CheckoutNewsletterSubscription\Api\Data\NewsletterSubscriptionInterface;
use Staempfli\CheckoutNewsletterSubscription\Api\NewsletterSubscriptionManagementInterface;
use Staempfli\CheckoutNewsletterSubscription\Model\Data\NewsletterSubscription;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class NewsletterSubscriptionManagement implements NewsletterSubscriptionManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var QuoteIdMask
     */
    private $quoteIdMaskResourceModel;

    public function __construct(
        QuoteIdMask $quoteIdMaskResourceModel,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
    }

    /**
     * @param string $cartId
     * @param NewsletterSubscriptionInterface $newsletterSubscription
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function subscribe($cartId, NewsletterSubscriptionInterface $newsletterSubscription)
    {
        if ((bool)$newsletterSubscription->getSubscribe()) {
            $quote = $this->getQuoteByCardId($cartId);

            try {
                $quote->setData(NewsletterSubscription::NEWSLETTER_SUBSCRIPTION, 1);
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('The order comment could not be saved'));
            }
        }
    }

    /**
     * @param $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuoteByCardId($cartId)
    {
        try {
            return $this->cartRepository->getActive($cartId);
        } catch (\Exception $e) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResourceModel->load($quoteIdMask, $cartId, 'masked_id');
            return $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        }
    }
}