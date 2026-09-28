<?php

$cityLogo = base64_encode(
    file_get_contents(__DIR__ . '/../assets/images/Mandaue LOGO.jpg')
);

$collegeLogo = base64_encode(
    file_get_contents(__DIR__ . '/../assets/images/MCC_NEW-v2.png')
);

?>
<!DOCTYPE html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mandaue City Health Referral Slip</title>
    <style>
    <?php

    echo file_get_contents(__DIR__ . '/referral_slip.css');
    ?>
    </style>
</head>
<body>
    <div class="referral-slip" id="pdfContent">
        <!-- HEADER SECTION -->
        <header>
            <div class="logo-box">
                <img src="data:image/jpeg;base64,<?= $cityLogo ?>" alt="City Logo" class="logo1">
            </div>
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <p class="bold" style="font-family: 'Times New Roman', Times, serif;">CITY OF MANDAUE</p>
                <p class="bold large">MANDAUE CITY COLLEGE</p>
                <p class="small">Door E1, Mandaue City Cultural and Sports Complex</p>
                <p class="small">Don Andres Soriano Ave., Centro, Mandaue City</p>
                <p class="small">E-mail add: <span class="underline">mc.college@hotmail.com</span></p>
            </div>
            <div class="logo-box">
                <img src="data:image/png;base64,<?= $collegeLogo ?>" alt="College Logo" class="logo2">
            </div>
        </header>

        <h2 class="form-title">MANDAUE CITY HEALTH REFERRAL SYSTEM</h2>

        <!-- PATIENT INFORMATION SECTION -->
        <div class="info-section">
            <div class="row">
                <div class="col" style="flex: 2;">
                    NAME: <span class="field-line" id="tName" style="width: 70%;">
                            <span class="field-value">
                                <?= htmlspecialchars($referral['full_name'] ?? '') ?>
                            </span>
                          </span>
                </div>
                <div class="col" style="flex: 1; text-align: left; padding-left: 110px;">
                    PHIC #
                        (<span class="checkmark"><?= ((int)$referral['is_philhealth_member'] === 1) ? '&#10003;' : '' ?></span>) YES
                        (<span class="checkmark"><?= ((int)$referral['is_philhealth_member'] === 0) ? '&#10003;' : '' ?></span>) NO
                        <span class="philhealth-number">
                            <?php if ((int)$referral['is_philhealth_member'] === 1): ?>
                                <?= htmlspecialchars($referral['philhealth_number'] ?? '') ?>
                            <?php endif; ?>
                        </span>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    ADDRESS: <span class="field-line" id="tAddress" style="width: 310px;">
                                <span class="field-value">
                                    <?= htmlspecialchars($referral['address'] ?? '') ?>
                                </span>
                             </span>
                </div>
            </div>

            <div class="rows">
                <div class="col">
                    DATE OF BIRTH: <span class="field-line" id="tDob" style="width: 150px;">
                                        <span class="field-value">
                                            <?= htmlspecialchars($referral['date_of_birth'] ?? '') ?>
                                        </span>
                                   </span>
                </div>
                <div class="col" style="padding-left: 110px;">
                    AGE: <span class="field-line" id="tAge" style="width: 85px;">
                            <span class="field-value">
                                <?php
                                if (!empty($referral['date_of_birth'])) {
                                    $dob = new DateTime($referral['date_of_birth']);
                                    $today = new DateTime();
                                    echo $today->diff($dob)->y;
                                }
                                ?>
                            </span>
                         </span>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    NAME OF FATHER/ MOTHER: <span class="field-line" id="tParent" style="width: 215px;">
                                                <span class="field-value">
                                                    <?= htmlspecialchars($referral['parent_name'] ?? '') ?>
                                                </span>
                                            </span>
                </div>
                <div class="col" style="padding-left: 110px;">
                    HUSBAND/WIFE: <span class="field-line" id="tSpouse" style="width: 150px;">
                                    <span class="field-value">
                                        <?= htmlspecialchars($referral['spouse_name'] ?? '') ?>
                                    </span>
                                  </span>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    DATE OF BIRTH: <span class="field-line" id="tParentDob" style="width: 280px;">
                                        <span class="field-value">
                                            <?= ($referral['parent_dob'] ?? '') !== '0000-00-00'
                                                ? htmlspecialchars($referral['parent_dob'] ?? '')
                                                : '' ?>
                                        </span>
                                   </span>
                </div>
                <div class="col" style="padding-left: 110px;">
                    DATE OF BIRTH: <span class="field-line" id="tSpouseDob" style="width: 150px;">
                                        <span class="field-value">
                                            <?= ($referral['spouse_dob'] ?? '') !== '0000-00-00'
                                                ? htmlspecialchars($referral['spouse_dob'] ?? '')
                                                : '' ?>
                                        </span>
                                   </span>
                </div>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 20%;">DATE</th>
                    <th style="width: 35%;">DOCTOR'S NAME</th>
                    <th style="width: 45%;">NURSE'S NOTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td> <?= htmlspecialchars($referral['referred_date'] ?? '') ?> </td>
                    <td> <?= htmlspecialchars($referral['doctor_name'] ?? '') ?> </td>
                    <td class="nurse-note-cell">
                        <div class="nurse-notes">
                            <?= nl2br(htmlspecialchars($referral['nurse_notes'] ?? '')) ?>
                        </div>

                        <div class="vitals">
                            <p>WT: <span class="field-line" id="tWeight" style="width: 80px;">
                                    <span class="field-value">
                                        <?= !empty($referral['weight']) ? rtrim(rtrim(number_format((float)$referral['weight'], 2, '.', ''), '0'), '.') . ' kg' : '' ?>
                                    </span>
                                </span>
                            </p>
                            <p>BP: <span class="field-line" id="tBP" style="width: 80px;">
                                        <span class="field-value">
                                            <?= htmlspecialchars($referral['blood_pressure'] ?? '') ?>
                                        </span>
                                   </span>
                            </p>
                            <p>TEMP: 
                                <span class="field-line" id="tTemp" style="width: 80px;">
                                    <span class="field-value">
                                        <?= !empty($referral['temperature']) ? htmlspecialchars($referral['temperature']) . ' °C' : '' ?>
                                    </span>
                                </span>
                            </p>
                        </div>
                    </td>
                </tr>

                <tr class="separator-row">
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td class="signatory-cell">
                        <div class="signatory">
                            <p class="bold underline">CLARELYN A. DIPAY, RN</p>
                            <p>COLLEGE NURSE</p>
                            <p>MANDAUE CITY COLLEGE</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>