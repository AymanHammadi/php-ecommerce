<nav class="navbar navbar-expand-lg border-bottom border-body navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="<?php echo $images; ?>PHPeco.png" alt="PHPECO-LOGO" width="200">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Nav Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link " href="/admin"><?= t('admin.nav.home') ?></a></li>
                <li class="nav-item"><a class="nav-link " href=""><?= t('admin.nav.dashboard') ?></a></li>
                <li class="nav-item"><a class="nav-link " href="#"><?= t('admin.nav.orders') ?></a></li>
                <li class="nav-item"><a class="nav-link " href="#"><?= t('admin.nav.products') ?></a></li>
                <li class="nav-item"><a class="nav-link " href="categories"><?= t('admin.nav.categories') ?></a></li>
                <li class="nav-item"><a class="nav-link " href="users"><?= t('admin.nav.users') ?></a></li>
            </ul>

            <!-- Account + Language -->
            <ul class="navbar-nav ms-auto align-items mb-2 mb-lg-0">
                <!-- Language Switcher -->
                <li class="nav-item">
                    <a class="nav-link px-2" href="<?= current_url_with_lang('ar') ?>"
                       style="font-weight: <?= (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en') === 'ar' ? '600' : '400' ?>">عربي</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="<?= current_url_with_lang('en') ?>"
                       style="font-weight: <?= (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en') === 'en' ? '600' : '400' ?>">English</a>
                </li>

                <!-- Account Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3" href="#" id="userDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><?= t('admin.nav.settings') ?></a></li>
                        <li><a class="dropdown-item"
                               href="users?do=Edit&id=<?= $_SESSION['user_id'] ?>">
                                <?= t('admin.nav.edit_profile') ?>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><?= t('admin.nav.logout') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
