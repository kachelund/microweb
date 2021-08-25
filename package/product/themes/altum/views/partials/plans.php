<?php defined('ALTUMCODE') || die() ?>

<?php

use Altum\Middlewares\Authentication;

?>

<?php if(settings()->payment->is_enabled): ?>

    <?php
    $plans = [];
    $available_payment_frequencies = [];

    $plans_result = database()->query("SELECT * FROM `plans` WHERE `status` = 1 ORDER BY `order`");

    while($plan = $plans_result->fetch_object()) {
        $plans[] = $plan;

        foreach(['monthly', 'annual', 'lifetime'] as $value) {
            if($plan->{$value . '_price'}) {
                $available_payment_frequencies[$value] = true;
            }
        }
    }

    ?>

    <?php if(count($plans)): ?>
        <div class="mb-5 d-flex justify-content-center">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">

                <?php if(isset($available_payment_frequencies['monthly'])): ?>
                    <label class="btn btn-outline-primary-900 active" data-payment-frequency="monthly">
                        <input type="radio" name="payment_frequency" checked="checked"> <?= language()->plan->custom_plan->monthly ?>
                    </label>
                <?php endif ?>

                <?php if(isset($available_payment_frequencies['annual'])): ?>
                    <label class="btn btn-outline-primary-900 <?= !isset($available_payment_frequencies['monthly']) ? 'active' : null ?>" data-payment-frequency="annual">
                        <input type="radio" name="payment_frequency" <?= !isset($available_payment_frequencies['monthly']) ? 'checked="checked"' : null ?>> <?= language()->plan->custom_plan->annual ?>
                    </label>
                <?php endif ?>

                <?php if(isset($available_payment_frequencies['lifetime'])): ?>
                    <label class="btn btn-outline-primary-900 <?= !isset($available_payment_frequencies['monthly']) && !isset($available_payment_frequencies['annual']) ? 'active' : null ?>" data-payment-frequency="lifetime">
                        <input type="radio" name="payment_frequency" <?= !isset($available_payment_frequencies['monthly']) && !isset($available_payment_frequencies['annual']) ? 'checked="checked"' : null ?>> <?= language()->plan->custom_plan->lifetime ?>
                    </label>
                <?php endif ?>

            </div>
        </div>
    <?php endif ?>
<?php endif ?>

<div class="pricing-container">
    <div class="pricing">

        <?php if(settings()->plan_free->status == 1): ?>

            <div class="pricing-plan">
                <div class="pricing-header">
                    <span class="pricing-name"><?= settings()->plan_free->name ?></span>

                    <div class="pricing-price">
                        <span class="pricing-price-amount"><?= language()->plan->free->price ?></span>
                    </div>

                    <div class="pricing-details">&nbsp;</div>
                </div>

                <div class="pricing-body">
                    <?= include_view(THEME_PATH . 'views/partials/plans_plan_content.php', ['plan_settings' => settings()->plan_free->settings]) ?>

                    <?php if(Authentication::check() && $this->user->plan_id == 'free'): ?>
                        <button class="btn btn-lg btn-block btn-secondary pricing-button"><?= language()->plan->button->already_free ?></button>
                    <?php else: ?>
                        <a href="<?= Authentication::check() ? url('pay/free') : url('register') ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= language()->plan->button->choose ?></a>
                    <?php endif ?>
                </div>
            </div>

        <?php endif ?>

        <?php if(settings()->payment->is_enabled): ?>

            <?php if(settings()->plan_trial->status == 1): ?>

                <div class="pricing-plan">
                    <div class="pricing-header">
                        <span class="pricing-name"><?= settings()->plan_trial->name ?></span>

                        <div class="pricing-price">
                            <span class="pricing-price-amount"><?= language()->plan->trial->price ?></span>
                        </div>

                        <div class="pricing-details">&nbsp;</div>
                    </div>

                    <div class="pricing-body">
                        <?= include_view(THEME_PATH . 'views/partials/plans_plan_content.php', ['plan_settings' => settings()->plan_trial->settings]) ?>

                        <?php if(Authentication::check() && $this->user->plan_trial_done): ?>
                            <button class="btn btn-lg btn-block btn-secondary pricing-button"><?= language()->plan->button->disabled ?></button>
                        <?php else: ?>
                            <a href="<?= Authentication::check() ? url('pay/trial') : url('register?redirect=pay/trial') ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= language()->plan->button->choose ?></a>
                        <?php endif ?>
                    </div>
                </div>

            <?php endif ?>

            <?php foreach($plans as $plan): ?>

                <?php $plan->settings = json_decode($plan->settings) ?>

                <div
                    class="pricing-plan"
                    data-plan-monthly="<?= json_encode((bool) $plan->monthly_price) ?>"
                    data-plan-annual="<?= json_encode((bool) $plan->annual_price) ?>"
                    data-plan-lifetime="<?= json_encode((bool) $plan->lifetime_price) ?>"
                >
                    <div class="pricing-header">
                        <span class="pricing-name"><?= $plan->name ?></span>

                        <div class="pricing-price">
                            <span class="pricing-price-amount d-none" data-plan-payment-frequency="monthly"><?= $plan->monthly_price ?></span>
                            <span class="pricing-price-amount d-none" data-plan-payment-frequency="annual"><?= $plan->annual_price ?></span>
                            <span class="pricing-price-amount d-none" data-plan-payment-frequency="lifetime"><?= $plan->lifetime_price ?></span>
                            <span class="pricing-price-currency"><?= settings()->payment->currency ?></span>
                        </div>

                        <div class="pricing-details">
                            <span class="d-none" data-plan-payment-frequency="monthly"><?= language()->plan->custom_plan->monthly_payments ?></span>
                            <span class="d-none" data-plan-payment-frequency="annual"><?= language()->plan->custom_plan->annual_payments ?></span>
                            <span class="d-none" data-plan-payment-frequency="lifetime"><?= language()->plan->custom_plan->lifetime_payments ?></span>
                        </div>
                    </div>

                    <div class="pricing-body">
                        <?= include_view(THEME_PATH . 'views/partials/plans_plan_content.php', ['plan_settings' => $plan->settings]) ?>

                        <a href="<?= Authentication::check() ? url('pay/' . $plan->plan_id) : url('register?redirect=pay/' . $plan->plan_id) ?>" class="btn btn-lg btn-block btn-primary pricing-button"><?= language()->plan->button->choose ?></a>
                    </div>
                </div>

            <?php endforeach ?>

            <?php ob_start() ?>
            <script>
                'use strict';

                let payment_frequency_handler = (event = null) => {

                    let payment_frequency = null;

                    if(event) {
                        payment_frequency = $(event.currentTarget).data('payment-frequency');
                    } else {
                        payment_frequency = $('[name="payment_frequency"]:checked').closest('label').data('payment-frequency');
                    }

                    switch(payment_frequency) {
                        case 'monthly':
                            $(`[data-plan-payment-frequency="annual"]`).removeClass('d-inline-block').addClass('d-none');
                            $(`[data-plan-payment-frequency="lifetime"]`).removeClass('d-inline-block').addClass('d-none');

                            break;

                        case 'annual':
                            $(`[data-plan-payment-frequency="monthly"]`).removeClass('d-inline-block').addClass('d-none');
                            $(`[data-plan-payment-frequency="lifetime"]`).removeClass('d-inline-block').addClass('d-none');

                            break

                        case 'lifetime':
                            $(`[data-plan-payment-frequency="monthly"]`).removeClass('d-inline-block').addClass('d-none');
                            $(`[data-plan-payment-frequency="annual"]`).removeClass('d-inline-block').addClass('d-none');

                            break
                    }

                    $(`[data-plan-payment-frequency="${payment_frequency}"]`).addClass('d-inline-block');

                    $(`[data-plan-${payment_frequency}="true"]`).removeClass('d-none').addClass('');
                    $(`[data-plan-${payment_frequency}="false"]`).addClass('d-none').removeClass('');

                };

                $('[data-payment-frequency]').on('click', payment_frequency_handler);

                payment_frequency_handler();
            </script>
            <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

        <?php endif ?>

    </div>
</div>











