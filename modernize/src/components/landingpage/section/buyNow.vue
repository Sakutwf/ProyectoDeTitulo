<template>
    <div class="bg-primary pt-sm-12 pt-10 pb-6 mt-sm-8">
        <v-container class="maxWidth">
            <v-row class="justify-space-between">
                <v-col cols="12" md="5">
                    <h2 class="text-h2 font-weight-bold mt-10 mb-6">Build your app with our highly customizable Vue based
                        Dashboard</h2>
                    <div class="d-sm-flex gap-3">
                        <v-btn class="bg-surface text-primary btn-custom-md m-btn-full" size="large" target="_blank"
                            to="/auth/login" flat>Login</v-btn>
                        <v-btn class="btn-custom-md m-btn-full mt-sm-0 mt-4" size="large" variant="outlined" target="_blank"
                             to="/auth/register" flat>Register</v-btn>
                    </div>
                </v-col>
                <v-col cols="12" md="5">
                    <div class="d-flex justify-md-end justify-center">
                        <img src="@/assets/images/landingpage/background/c2a.png" alt="image" width="330" />
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </div>
    <div class="mt-n16">
        <v-container class="maxWidth">
            <v-row>
                <v-col cols="12">
                    <v-card elevation="10">
                        <div class="py-6 px-6 px-sm-9 d-sm-flex align-center money-back mt-n7">
                            <div class="d-flex align-center gap-4">
                                <img src="@/assets/images/landingpage/shape/badge.svg" alt="icon" />
                                <div class="">
                                    <h4 class="text-h4">100% moneyback guarantee</h4>
                                    <h6 class="text-subtitle-1 mt-1">We offer 48 hours moneyback guarantee.</h6>
                                </div>
                            </div>
                            <v-btn class="ml-auto mt-sm-0 mt-4 btn-custom-md m-btn-full" color="primary" size="large"
                                target="_blank" href="" flat>Contact</v-btn>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
